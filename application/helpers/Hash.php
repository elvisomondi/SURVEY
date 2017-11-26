<?php

class Hash {


	public static function get(array $data, $path) {
		if (empty($data)) {
			return null;
		}
		if (is_string($path) || is_numeric($path)) {
			$parts = explode('.', $path);
		} else {
			$parts = $path;
		}
		foreach ($parts as $key) {
			if (is_array($data) && isset($data[$key])) {
				$data =& $data[$key];
			} else {
				return null;
			}

		}
		return $data;
	}

	public static function extract(array $data, $path) {
		if (empty($path)) {
			return $data;
		}

		// Simple paths.
		if (!preg_match('/[{\[]/', $path)) {
			return (array)self::get($data, $path);
		}

		if (strpos($path, '[') === false) {
			$tokens = explode('.', $path);
		} else {
			$tokens = String::tokenize($path, '.', '[', ']');
		}

		$_key = '__set_item__';

		$context = array($_key => array($data));

		foreach ($tokens as $token) {
			$next = array();

			$conditions = false;
			$position = strpos($token, '[');
			if ($position !== false) {
				$conditions = substr($token, $position);
				$token = substr($token, 0, $position);
			}

			foreach ($context[$_key] as $item) {
				foreach ((array)$item as $k => $v) {
					if (self::_matchToken($k, $token)) {
						$next[] = $v;
					}
				}
			}

			// Filter for attributes.
			if ($conditions) {
				$filter = array();
				foreach ($next as $item) {
					if (self::_matches($item, $conditions)) {
						$filter[] = $item;
					}
				}
				$next = $filter;
			}
			$context = array($_key => $next);

		}
		return $context[$_key];
	}

/**
 * Check a key against a token.
 *
 * @param string $key The key in the array being searched.
 * @param string $token The token being matched.
 * @return boolean
 */
	protected static function _matchToken($key, $token) {
		if ($token === '{n}') {
			return is_numeric($key);
		}
		if ($token === '{s}') {
			return is_string($key);
		}
		if (is_numeric($token)) {
			return ($key == $token);
		}
		return ($key === $token);
	}

/**
 * Checks whether or not $data matches the attribute patterns
 *
 * @param array $data Array of data to match.
 * @param string $selector The patterns to match.
 * @return boolean Fitness of expression.
 */
	protected static function _matches(array $data, $selector) {
		preg_match_all(
			'/(\[ (?<attr>[^=><!]+?) (\s* (?<op>[><!]?[=]|[><]) \s* (?<val>(?:\/.*?\/ | [^\]]+)) )? \])/x',
			$selector,
			$conditions,
			PREG_SET_ORDER
		);

		foreach ($conditions as $cond) {
			$attr = $cond['attr'];
			$op = isset($cond['op']) ? $cond['op'] : null;
			$val = isset($cond['val']) ? $cond['val'] : null;

			// Presence test.
			if (empty($op) && empty($val) && !isset($data[$attr])) {
				return false;
			}

			// Empty attribute = fail.
			if (!(isset($data[$attr]) || array_key_exists($attr, $data))) {
				return false;
			}

			$prop = isset($data[$attr]) ? $data[$attr] : null;

			// Pattern matches and other operators.
			if ($op === '=' && $val && $val[0] === '/') {
				if (!preg_match($val, $prop)) {
					return false;
				}
			} elseif (
				($op === '=' && $prop != $val) ||
				($op === '!=' && $prop == $val) ||
				($op === '>' && $prop <= $val) ||
				($op === '<' && $prop >= $val) ||
				($op === '>=' && $prop < $val) ||
				($op === '<=' && $prop > $val)
			) {
				return false;
			}

		}
		return true;
	}


	public static function insert(array $data, $path, $values = null) {
		$tokens = explode('.', $path);
		if (strpos($path, '{') === false) {
			return self::_simpleOp('insert', $data, $tokens, $values);
		}

		$token = array_shift($tokens);
		$nextPath = implode('.', $tokens);
		foreach ($data as $k => $v) {
			if (self::_matchToken($k, $token)) {
				$data[$k] = self::insert($v, $nextPath, $values);
			}
		}
		return $data;
	}


	protected static function _simpleOp($op, $data, $path, $values = null) {
		$_list =& $data;

		$count = count($path);
		$last = $count - 1;
		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key === '0') {
				$key = intval($key);
			}
			if ($op === 'insert') {
				if ($i === $last) {
					$_list[$key] = $values;
					return $data;
				}
				if (!isset($_list[$key])) {
					$_list[$key] = array();
				}
				$_list =& $_list[$key];
				if (!is_array($_list)) {
					$_list = array();
				}
			} elseif ($op === 'remove') {
				if ($i === $last) {
					unset($_list[$key]);
					return $data;
				}
				if (!isset($_list[$key])) {
					return $data;
				}
				$_list =& $_list[$key];
			}
		}
	}

/**
 * Remove data matching $path from the $data array.
 * You can use `{n}` and `{s}` to remove multiple elements
 * from $data.
 *
 * @param array $data The data to operate on
 * @param string $path A path expression to use to remove.
 * @return array The modified array.
 */
	public static function remove(array $data, $path) {
		$tokens = explode('.', $path);
		if (strpos($path, '{') === false) {
			return self::_simpleOp('remove', $data, $tokens);
		}

		$token = array_shift($tokens);
		$nextPath = implode('.', $tokens);
		foreach ($data as $k => $v) {
			$match = self::_matchToken($k, $token);
			if ($match && is_array($v)) {
				$data[$k] = self::remove($v, $nextPath);
			} elseif ($match) {
				unset($data[$k]);
			}
		}
		return $data;
	}


	public static function combine(array $data, $keyPath, $valuePath = null, $groupPath = null) {
		if (empty($data)) {
			return array();
		}

		if (is_array($keyPath)) {
			$format = array_shift($keyPath);
			$keys = self::format($data, $keyPath, $format);
		} else {
			$keys = self::extract($data, $keyPath);
		}
		if (empty($keys)) {
			return array();
		}

		if (!empty($valuePath) && is_array($valuePath)) {
			$format = array_shift($valuePath);
			$vals = self::format($data, $valuePath, $format);
		} elseif (!empty($valuePath)) {
			$vals = self::extract($data, $valuePath);
		}

		$count = count($keys);
		for ($i = 0; $i < $count; $i++) {
			$vals[$i] = isset($vals[$i]) ? $vals[$i] : null;
		}

		if ($groupPath !== null) {
			$group = self::extract($data, $groupPath);
			if (!empty($group)) {
				$c = count($keys);
				for ($i = 0; $i < $c; $i++) {
					if (!isset($group[$i])) {
						$group[$i] = 0;
					}
					if (!isset($out[$group[$i]])) {
						$out[$group[$i]] = array();
					}
					$out[$group[$i]][$keys[$i]] = $vals[$i];
				}
				return $out;
			}
		}
		if (empty($vals)) {
			return array();
		}
		return array_combine($keys, $vals);
	}


	public static function format(array $data, array $paths, $format) {
		$extracted = array();
		$count = count($paths);

		if (!$count) {
			return;
		}

		for ($i = 0; $i < $count; $i++) {
			$extracted[] = self::extract($data, $paths[$i]);
		}
		$out = array();
		$data = $extracted;
		$count = count($data[0]);

		$countTwo = count($data);
		for ($j = 0; $j < $count; $j++) {
			$args = array();
			for ($i = 0; $i < $countTwo; $i++) {
				if (array_key_exists($j, $data[$i])) {
					$args[] = $data[$i][$j];
				}
			}
			$out[] = vsprintf($format, $args);
		}
		return $out;
	}


	public static function contains(array $data, array $needle) {
		if (empty($data) || empty($needle)) {
			return false;
		}
		$stack = array();

		while (!empty($needle)) {
			$key = key($needle);
			$val = $needle[$key];
			unset($needle[$key]);

			if (isset($data[$key]) && is_array($val)) {
				$next = $data[$key];
				unset($data[$key]);

				if (!empty($val)) {
					$stack[] = array($val, $next);
				}
			} elseif (!isset($data[$key]) || $data[$key] != $val) {
				return false;
			}

			if (empty($needle) && !empty($stack)) {
				list($needle, $data) = array_pop($stack);
			}
		}
		return true;
	}

	public static function check(array $data, $path) {
		$results = self::extract($data, $path);
		if (!is_array($results)) {
			return false;
		}
		return count($results) > 0;
	}

	public static function filter(array $data, $callback = array('self', '_filter')) {
		foreach ($data as $k => $v) {
			if (is_array($v)) {
				$data[$k] = self::filter($v, $callback);
			}
		}
		return array_filter($data, $callback);
	}

/**
 * Callback function for filtering.
 *
 * @param array $var Array to filter.
 * @return boolean
 */
	protected static function _filter($var) {
		if ($var === 0 || $var === '0' || !empty($var)) {
			return true;
		}
		return false;
	}


	public static function flatten(array $data, $separator = '.') {
		$result = array();
		$stack = array();
		$path = null;

		reset($data);
		while (!empty($data)) {
			$key = key($data);
			$element = $data[$key];
			unset($data[$key]);

			if (is_array($element) && !empty($element)) {
				if (!empty($data)) {
					$stack[] = array($data, $path);
				}
				$data = $element;
				reset($data);
				$path .= $key . $separator;
			} else {
				$result[$path . $key] = $element;
			}

			if (empty($data) && !empty($stack)) {
				list($data, $path) = array_pop($stack);
				reset($data);
			}
		}
		return $result;
	}


	public static function expand($data, $separator = '.') {
		$result = array();
		foreach ($data as $flat => $value) {
			$keys = explode($separator, $flat);
			$keys = array_reverse($keys);
			$child = array(
				$keys[0] => $value
			);
			array_shift($keys);
			foreach ($keys as $k) {
				$child = array(
					$k => $child
				);
			}
			$result = self::merge($result, $child);
		}
		return $result;
	}


	public static function merge(array $data, $merge) {
		$args = func_get_args();
		$return = current($args);

		while (($arg = next($args)) !== false) {
			foreach ((array)$arg as $key => $val) {
				if (!empty($return[$key]) && is_array($return[$key]) && is_array($val)) {
					$return[$key] = self::merge($return[$key], $val);
				} elseif (is_int($key) && isset($return[$key])) {
					$return[] = $val;
				} else {
					$return[$key] = $val;
				}
			}
		}
		return $return;
	}

	public static function numeric(array $data) {
		if (empty($data)) {
			return false;
		}
		$values = array_values($data);
		$str = implode('', $values);
		return (bool)ctype_digit($str);
	}


	public static function dimensions(array $data) {
		if (empty($data)) {
			return 0;
		}
		reset($data);
		$depth = 1;
		while ($elem = array_shift($data)) {
			if (is_array($elem)) {
				$depth += 1;
				$data =& $elem;
			} else {
				break;
			}
		}
		return $depth;
	}


	public static function maxDimensions(array $data) {
		$depth = array();
		if (is_array($data) && reset($data) !== false) {
			foreach ($data as $value) {
				$depth[] = self::dimensions((array)$value) + 1;
			}
		}
		return max($depth);
	}

	public static function map(array $data, $path, $function) {
		$values = (array)self::extract($data, $path);
		return array_map($function, $values);
	}


	public static function reduce(array $data, $path, $function) {
		$values = (array)self::extract($data, $path);
		return array_reduce($values, $function);
	}


	public static function apply(array $data, $path, $function) {
		$values = (array)self::extract($data, $path);
		return call_user_func($function, $values);
	}

	public static function sort(array $data, $path, $dir, $type = 'regular') {
		if (empty($data)) {
			return array();
		}
		$originalKeys = array_keys($data);
		$numeric = is_numeric(implode('', $originalKeys));
		if ($numeric) {
			$data = array_values($data);
		}
		$sortValues = self::extract($data, $path);
		$sortCount = count($sortValues);
		$dataCount = count($data);

		// Make sortValues match the data length, as some keys could be missing
		// the sorted value path.
		if ($sortCount < $dataCount) {
			$sortValues = array_pad($sortValues, $dataCount, null);
		}
		$result = self::_squash($sortValues);
		$keys = self::extract($result, '{n}.id');
		$values = self::extract($result, '{n}.value');

		$dir = strtolower($dir);
		$type = strtolower($type);
		if ($type === 'natural' && version_compare(PHP_VERSION, '5.4.0', '<')) {
			$type = 'regular';
		}
		if ($dir === 'asc') {
			$dir = SORT_ASC;
		} else {
			$dir = SORT_DESC;
		}
		if ($type === 'numeric') {
			$type = SORT_NUMERIC;
		} elseif ($type === 'string') {
			$type = SORT_STRING;
		} elseif ($type === 'natural') {
			$type = SORT_NATURAL;
		} else {
			$type = SORT_REGULAR;
		}
		array_multisort($values, $dir, $type, $keys, $dir, $type);
		$sorted = array();
		$keys = array_unique($keys);

		foreach ($keys as $k) {
			if ($numeric) {
				$sorted[] = $data[$k];
				continue;
			}
			if (isset($originalKeys[$k])) {
				$sorted[$originalKeys[$k]] = $data[$originalKeys[$k]];
			} else {
				$sorted[$k] = $data[$k];
			}
		}
		return $sorted;
	}

	protected static function _squash($data, $key = null) {
		$stack = array();
		foreach ($data as $k => $r) {
			$id = $k;
			if (!is_null($key)) {
				$id = $key;
			}
			if (is_array($r) && !empty($r)) {
				$stack = array_merge($stack, self::_squash($r, $id));
			} else {
				$stack[] = array('id' => $id, 'value' => $r);
			}
		}
		return $stack;
	}


	public static function diff(array $data, $compare) {
		if (empty($data)) {
			return (array)$compare;
		}
		if (empty($compare)) {
			return (array)$data;
		}
		$intersection = array_intersect_key($data, $compare);
		while (($key = key($intersection)) !== null) {
			if ($data[$key] == $compare[$key]) {
				unset($data[$key]);
				unset($compare[$key]);
			}
			next($intersection);
		}
		return $data + $compare;
	}


	public static function mergeDiff(array $data, $compare) {
		if (empty($data) && !empty($compare)) {
			return $compare;
		}
		if (empty($compare)) {
			return $data;
		}
		foreach ($compare as $key => $value) {
			if (!array_key_exists($key, $data)) {
				$data[$key] = $value;
			} elseif (is_array($value)) {
				$data[$key] = self::mergeDiff($data[$key], $compare[$key]);
			}
		}
		return $data;
	}


	public static function normalize(array $data, $assoc = true) {
		$keys = array_keys($data);
		$count = count($keys);
		$numeric = true;

		if (!$assoc) {
			for ($i = 0; $i < $count; $i++) {
				if (!is_int($keys[$i])) {
					$numeric = false;
					break;
				}
			}
		}
		if (!$numeric || $assoc) {
			$newList = array();
			for ($i = 0; $i < $count; $i++) {
				if (is_int($keys[$i])) {
					$newList[$data[$keys[$i]]] = null;
				} else {
					$newList[$keys[$i]] = $data[$keys[$i]];
				}
			}
			$data = $newList;
		}
		return $data;
	}


	public static function nest(array $data, $options = array()) {
		if (!$data) {
			return $data;
		}

		$alias = key(current($data));
		$options += array(
			'idPath' => "{n}.$alias.id",
			'parentPath' => "{n}.$alias.parent_id",
			'children' => 'children',
			'root' => null
		);

		$return = $idMap = array();
		$ids = self::extract($data, $options['idPath']);

		$idKeys = explode('.', $options['idPath']);
		array_shift($idKeys);

		$parentKeys = explode('.', $options['parentPath']);
		array_shift($parentKeys);

		foreach ($data as $result) {
			$result[$options['children']] = array();

			$id = self::get($result, $idKeys);
			$parentId = self::get($result, $parentKeys);

			if (isset($idMap[$id][$options['children']])) {
				$idMap[$id] = array_merge($result, (array)$idMap[$id]);
			} else {
				$idMap[$id] = array_merge($result, array($options['children'] => array()));
			}
			if (!$parentId || !in_array($parentId, $ids)) {
				$return[] =& $idMap[$id];
			} else {
				$idMap[$parentId][$options['children']][] =& $idMap[$id];
			}
		}

		if ($options['root']) {
			$root = $options['root'];
		} else {
			$root = self::get($return[0], $parentKeys);
		}

		foreach ($return as $i => $result) {
			$id = self::get($result, $idKeys);
			$parentId = self::get($result, $parentKeys);
			if ($id !== $root && $parentId != $root) {
				unset($return[$i]);
			}
		}
		return array_values($return);
	}

}
