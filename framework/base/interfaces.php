<?php
/**
 * IApplicationComponent is the interface that all application components must implement
 */
interface IApplicationComponent
{
	/**
	 * Initializes the application component.
	 * This method is invoked after the application completes configuration.
	 */
	public function init();
	
	public function getIsInitialized();
}


interface ICache
{
	
	public function get($id);
	/**
	 * Retrieves multiple values from cache with the specified keys.
	 */
	public function mget($ids);
	/**
	 * Stores a value identified by a key into cache.
	 */
	public function set($id,$value,$expire=0,$dependency=null);
	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 */
	public function add($id,$value,$expire=0,$dependency=null);
	/**
	 * Deletes a value with the specified key from cache
	 */
	public function delete($id);
	/**
	 * Deletes all values from cache.
	 */
	public function flush();
}


interface ICacheDependency
{
	
	public function evaluateDependency();
	
	public function getHasChanged();
}



interface IStatePersister
{
	/**
	 * Loads state data from a persistent storage.
	 * @return mixed the state
	 */
	public function load();
	/**
	 * Saves state data into a persistent storage.
	 * @param mixed $state the state to be saved
	 */
	public function save($state);
}



interface IFilter
{
	
	public function filter($filterChain);
}


interface IAction
{
	/**
	 * @return string id of the action
	 */
	public function getId();
	/**
	 * @return CController the controller instance
	 */
	public function getController();
}



interface IWebServiceProvider
{
	
	public function beforeWebMethod($service);
	/**
	 * This method is invoked after the requested remote method is invoked.
	 * @param CWebService $service the currently requested Web service.
	 */
	public function afterWebMethod($service);
}


interface IViewRenderer
{
	/**
	 * Renders a view file.
	 */
	public function renderFile($context,$file,$data,$return);
}


interface IUserIdentity
{
	/**
	 * Authenticates the user.
	 */
	public function authenticate();
	
	public function getIsAuthenticated();
	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	public function getId();
	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 */
	public function getName();
	/**
	 * Returns the additional identity information that needs to be persistent during the user session.
	 * @return array additional identity information that needs to be persistent during the user session (excluding {@link id}).
	 */
	public function getPersistentStates();
}


interface IWebUser
{
	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	public function getId();
	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 */
	public function getName();
	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the user is a guest (not authenticated)
	 */
	public function getIsGuest();
	
	public function checkAccess($operation,$params=array());
	/**
	 * Redirects the user browser to the login page.
	 */
	public function loginRequired();
}



interface IAuthManager
{
	
	public function checkAccess($itemName,$userId,$params=array());

	
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null);
	
	public function removeAuthItem($name);
	
	public function getAuthItems($type=null,$userId=null);
	
	public function getAuthItem($name);
	/**
	 * Saves an authorization item to persistent storage.
	.
	 */
	public function saveAuthItem($item,$oldName=null);

	/**
	 * Adds an item as a child of another item.
	 
	 */
	public function addItemChild($itemName,$childName);
	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName,$childName);
	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName,$childName);
	/**
	 * Returns the children of the specified item.
	 * @param mixed $itemName the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return array all child items of the parent
	 */
	public function getItemChildren($itemName);

	
	public function assign($itemName,$userId,$bizRule=null,$data=null);
	
	public function revoke($itemName,$userId);
	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 
	 */
	public function isAssigned($itemName,$userId);
	/**
	 * Returns the item assignment information.
	 */
	public function getAuthAssignment($itemName,$userId);
	/**
	 * Returns the item assignments for the specified user.
	 */
	public function getAuthAssignments($userId);
	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment $assignment the assignment that has been changed.
	 */
	public function saveAuthAssignment($assignment);

	/**
	 * Removes all authorization data.
	 */
	public function clearAll();
	/**
	 * Removes all authorization assignments.
	 */
	public function clearAuthAssignments();

	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	public function save();

	/**
	 * Executes a business rule.
	 */
	public function executeBizRule($bizRule,$params,$data);
}



interface IBehavior
{
	/**
	 * Attaches the behavior object to the component.
	 * @param CComponent $component the component that this behavior is to be attached to.
	 */
	public function attach($component);
	/**
	 * Detaches the behavior object from the component.
	 * @param CComponent $component the component that this behavior is to be detached from.
	 */
	public function detach($component);
	/**
	 * @return boolean whether this behavior is enabled
	 */
	public function getEnabled();
	/**
	 * @param boolean $value whether this behavior is enabled
	 */
	public function setEnabled($value);
}


interface IWidgetFactory
{
	
	public function createWidget($owner,$className,$properties=array());
}


interface IDataProvider
{
	/**
	 * @return string the unique ID that identifies the data provider from other data providers.
	 */
	public function getId();
	
	public function getItemCount($refresh=false);
	
	public function getTotalItemCount($refresh=false);
	/**
	 * Returns the data items currently available.
	 * @param boolean $refresh whether the data should be re-fetched from persistent storage.
	 * @return array the list of data items currently available in this data provider.
	 */
	public function getData($refresh=false);
	/**
	 * Returns the key values associated with the data items.
	 * @param boolean $refresh whether the keys should be re-calculated.
	 * @return array the list of key values corresponding to {@link data}. Each data item in {@link data}
	 * is uniquely identified by the corresponding key value in this array.
	 */
	public function getKeys($refresh=false);
	/**
	 * @return CSort the sorting object. If this is false, it means the sorting is disabled.
	 */
	public function getSort();
	/**
	 * @return CPagination the pagination object. If this is false, it means the pagination is disabled.
	 */
	public function getPagination();
}


interface ILogFilter
{
	
	public function filter(&$logs);
}

