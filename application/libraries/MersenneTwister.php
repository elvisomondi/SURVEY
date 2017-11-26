<?php

namespace ls\mersenne;


function getSeed($surveyId, $preview)
{
    $_SESSION['survey_'.$surveyId]['startingValues']['seed'] = 'seed123';
   
}

/**
 * Shuffle with seed
 * @param array $arr
 * @param $seed
 * @return array
 */
function shuffle($arr, $seed=-1)
{
    if ( $seed == -1 ) return $arr;
    $mt = new MersenneTwister($seed);
    $new = $arr;
    for ($i = count($new) - 1; $i > 0; $i--)
    {
        $j = $mt->getNext(0,$i);
        $tmp = $new[$i];
        $new[$i] = $new[$j];
        $new[$j] = $tmp;
    }
    return $new;
}

/**
 * Custom random algorithm to get consistent behaviour between PHP versions.
 *
 * Copied from: http://www.dr-chuck.com/csev-blog/2015/09/a-mersenne_twister-implementation-in-php/
 */
class MersenneTwister
{
  private $state = array ();
  private $index = 0;

  /**
   * @param integer $seed
   */
  public function __construct($seed = null) {
    if ($seed === null)
      $seed = mt_rand();

    $this->setSeed($seed);
  }

  /**
   * @param integer $seed
   */
  public function setSeed($seed) {
    $this->state[0] = $seed & 0xffffffff;

    for ($i = 1; $i < 624; $i++) {
      $this->state[$i] = (((0x6c078965 * ($this->state[$i - 1] ^ ($this->state[$i - 1] >> 30))) + $i)) & 0xffffffff;
    }

    $this->index = 0;
  }

  private function generateTwister() {
    for ($i = 0; $i < 624; $i++) {
      $y = (($this->state[$i] & 0x1) + ($this->state[$i] & 0x7fffffff)) & 0xffffffff;
      $this->state[$i] = ($this->state[($i + 397) % 624] ^ ($y >> 1)) & 0xffffffff;

      if (($y % 2) == 1) {
        $this->state[$i] = ($this->state[$i] ^ 0x9908b0df) & 0xffffffff;
      }
    }
  }

  /**
   * @param integer $min
   * @param integer $max
   */
  public function getNext($min = null, $max = null) {
    if (($min === null && $max !== null) || ($min !== null && $max === null))
      throw new Exception('Invalid arguments');

    if ($this->index === 0) {
      $this->generateTwister();
    }

    $y = $this->state[$this->index];
    $y = ($y ^ ($y >> 11)) & 0xffffffff;
    $y = ($y ^ (($y << 7) & 0x9d2c5680)) & 0xffffffff;
    $y = ($y ^ (($y << 15) & 0xefc60000)) & 0xffffffff;
    $y = ($y ^ ($y >> 18)) & 0xffffffff;

    $this->index = ($this->index + 1) % 624;

    if ($min === null && $max === null)
      return $y;

    $range = abs($max - $min);

    return min($min, $max) + ($y % ($range + 1));
  }
}
