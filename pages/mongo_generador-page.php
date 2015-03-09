<?php 

	$singular = $_POST["dato1"];
	$plural = $_POST["dato2"];
	$json = $_POST["json"];

	//var_dump($json);
	$variables = json_decode($json);
	//var_dump($variables);

     


	//echo json_decode($json);

	$archivo = strtolower($singular) . ".model.php";
	$singular = ucfirst($singular);
	$plural = ucfirst($plural);
	$lin = "";
	$lin .= "<?php \n\n ";
	$lin .= "\tclass {$singular}";
	$lin .= " ";
	$lin .= "extends ";
	$lin .= "MongoModel {\n\n";

	foreach($variables as $key => $value) {
         //echo $key . " "; 
 		$lin .= "\t\tpublic \${$key};\n";

     }

	$lin .= "\n\t\t/**\n\t\t * Initialization callback\n";
	$lin .= "\t\t";
	$lin .= " * @return nothing\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tfunction init() {\n\n";

	$lin .= "\t\t\t\$this->collection_name = 'users';\n\n";
	$lin .= "\t\t\tif (! \$this->_id ) {\n\n";

	foreach ($variables as $key => $value) {
		$lin .= "\t\t\t\t\$this->{$key} = '';\n";
	}

	$lin .= "\t\t\t} else {\n\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t}\n";


	$lin .= "\n\t\t/**\n\t\t * Save the model\n";
	$lin .= "\t\t";
	$lin .= " * @return boolean True if the model was updated, False otherwise\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tfunction save() {\n\n";

	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$collection_name = \$this->collection_name;\n\n";

	$lin .= "\t\t\t# Hash the password if required\n";
	$lin .= "\t\t\tif( substr(\$this->password, 0, 4) != '\$2a\$' ) {\n";
	$lin .= "\t\t\t\t\$this->password = Users::hashPassword(\$this->password);\n";
	$lin .= "\t\t\t}\n\n";
	$lin .= "\t\t\t\$this->slug = \$this->slug ?  \$this->slug : \$site->toAscii(\$this->email);\n";
	$lin .= "\t\t\t\$this->nickname = \$this->nickname ?  \$this->nickname : \$this->email;\n";
	$lin .= "\t\t\t\$this->login = \$this->login ?  \$this->login : \$this->email;\n\n";

	$lin .= "\t\t\t\$this->modified = date('Y-m-d H:i:s');\n\n";
	$lin .= "\t\t\t# Sanitization\n";
	$lin .= "\t\t\tif ( empty(\$this->email) || empty(\$this->login) ) {\n";
	$lin .= "\t\t\t\treturn false;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\ttry {\n\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->\$collection_name->save(\$this);\n\n";
	$lin .= "\t\t\t\tif ( \$ret['ok'] ) {\n\n";
	$lin .= "\t\t\t\t\t\$ret = \$this\n";
	$lin .= "\t\t\t\t}\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t{\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";


	$lin .= "\n\t\t/**\n\t\t * Get the user's avatar URL and origin\n";
	$lin .= "\t\t * @param  string  \$type     Type of returned value, either 'url' or 'object'\n";
	$lin .= "\t\t * @param  boolean \$echo     Whether to echo the URL or not\n";
	$lin .= "\t\t * @return mixed             Object or string with URL, according to \$type\n";
	$lin .= "\t\t */\n";


	$lin .= "\t\tfunction getAvatar(\$type = 'url', \$echo = false) {\n\n";
	$lin .= "\t\t\t# Try to get from social network\n";
	$lin .= "\t\t\t\$provider = \$this->getMeta('provider');\n";
	$lin .= "\t\t\t\$avatar = \$this->getMeta('avatar');\n";
	$lin .= "\t\t\t\$ret = new stdClass();\n";
	$lin .= "\t\t\t\$ret->src = '';\n";
	$lin .= "\t\t\t\$ret->url = '';\n";
	$lin .= "\t\t\tif (\$avatar) {\n";
	$lin .= "\t\t\t\t# User has custom avatar, use it\n";
	$lin .= "\t\t\t\t\$attachment = Attachments::get(\$avatar);\n";
	$lin .= "\t\t\t\t\$ret->url = \$attachment ? \$attachment->getImage() : '';\n";
	$lin .= "\t\t\t\t\$ret->source = 'Attachment';\n";
	$lin .= "\t\t\t} else {\n";
	$lin .= "\t\t\t\t# User has not uploaded its avatar, try to guess it\n";
	$lin .= "\t\t\t\tif (\$provider) {\n";
	$lin .= "\t\t\t\t\t# OAuth provider, try to get avatar form there\n";
	$lin .= "\t\t\t\t\t\$ret->url = \$this->getMeta(strtolower(\$provider) . '_avatar');\n";
	$lin .= "\t\t\t\t\t\$ret->source = \$provider;\n";
	$lin .= "\t\t\t\t}\n";
	$lin .= "\t\t\t}\n";

	$lin .= "\t\t\t# Last resort Gravatar\n";
	$lin .= "\t\t\tif (!\$ret->url) {\n";
	$lin .= "\t\t\t\t# No avatar yet, use Gravatar\n";
	$lin .= "\t\t\t\t\$ret->url = get_gravatar(\$this->email);\n";
	$lin .= "\t\t\t\t\$ret->source = 'Gravatar';\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\t# Shall we echo it?\n";
	$lin .= "\t\t\tif (\$echo) {\n";
	$lin .= "\t\t\t\techo \$ret->url;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\t# Return the appropiate thing\n";
	$lin .= "\t\t\treturn \$type == 'url' ? \$ret->url : \$ret;\n";
	$lin .= "\t\t}\n";
	$lin .= "\t}\n\n";

	$lin .= "\t# ==============================================================================================\n\n";
	$lin .= "\t/**\n\t * Users Class\n";
	$lin .= "\t *\n";
	$lin .= "\t * Handles the user account mechanism.\n";
	$lin .= "\t *\n";
	$lin .= "\t * @version 1.0\n";
	$lin .= "\t * @author  Raul Vera <raul.vera@thewebchi.mp>\n";
	$lin .= "\t */\n";

	$lin .= "\tclass {$plural}\n\n";
	$lin .= "\t\tstatic protected \$user_id;\n";
	$lin .= "\t\tstatic protected \$roles;\n";
	$lin .= "\t\tstatic protected \$collection_name;\n\n";
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Initialization function\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function init() {\n";
	$lin .= "\t\t\tglobal \$site;\n\n";
	$lin .= "\t\t\tself::\$collection_name = 'users'\n\n";
	$lin .= "\t\t\t# Initialize some defaults\n";
	$lin .= "\t\t\tself::\$user_id = 0;\n";
	$lin .= "\t\t\tself::\$roles = array(;\n";
	$lin .= "\t\t\t\t'super_admin',\n";
	$lin .= "\t\t\t\t'admin',\n";
	$lin .= "\t\t\t\t'user',\n";
	$lin .= "\t\t\t);\n";
	$lin .= "\t\t\t# And hook the '/logout' route\n";
	$lin .= "\t\t\t\$site->addRoute('/logout', 'Users::_doLogout', true);\n";
	$lin .= "\t\t}\n\n";

	$lin .= "\t\t/**";
	$lin .= "\t\t * Shorthand get() method\n";
	$lin .= "\t\t * @param  mixed \$id  Numeric ID or string slug\n";
	$lin .= "\t\t * @return mixed      User object if the user was found, Null otherwise\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\t static function get(\$id, \$type = \"_id\") {\n";
	$lin .= "\t\t\tif(\$type == '_id')\n";
	$lin .= "\t\t\t\treturn self::_getBySlug(\$id);\n\n";
	$lin .= "\t\t\t\telse if(\$type == 'slug')\n";
	$lin .= "\t\t\t\t\treturn self::_Slug(\$id);\n";
	$lin .= "\t\t}\n\n";

	$lin .= "\t\tstatic function count(\$conditions = array()) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$collection_name = self::\$collection_name;\n";
	$lin .= "\t\t\t\$ret = 0;\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->collection_name->count(\$conditions);\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t\t\$site->errorMessage(\"Database error: {\$e->getCode()} in Users::count()\");\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";

	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Retrieve all the users from the database\n";
	$lin .= "\t\t * @return array      Array with User objects, False on error\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function all(\$page =1, \$show = 1000, \$sort = array()) {\n";
	$lin .= "\t\t\tglobal \$site;";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$collection_name = self::\$collection_name;\n";
	$lin .= "\t\t\t\$ret = array();\n";
	$lin .= "\t\t\t\$offset = \$show * (\$page - 1);\n";
	$lin .= "\t\t\t# Sanity checks\n";
	$lin .= "\t\t\t\$sort = strtoupper(\$sort);\n";
	$lin .= "\t\t\t\$sort = is_array(\$sort) ? \$sort : array();\n";
	$lin .= "\t\t\t\$offset = is_numeric(\$offset) ? \$offset : false;\n";
	$lin .= "\t\t\t\$show = is_numeric(\$show) ? \$show : false;\n";
	$lin .= "\t\t\tif (\$sort === false || \$offset === false || \$show === false) {\n";
	$lin .= "\t\t\t\t return \$ret;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->\$collection_name->find()->sort(\$sort)->skip(\$offset)->limit(\$show);\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t\t\$site->errorMessage(\"Database error: {\$e->getCode()} in Users::all()\");\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n";

	// function where
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Get filtered results\n";
	$lin .= "\t\t * @param  string \$column   Column name\n";
	$lin .= "\t\t * @param  string \$operator Comparison operator, defaults to '='\n";
	$lin .= "\t\t * @param  string \$value    Column value\n";
	$lin .= "\t\t * @return array            Array with filtered results\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function where(\$conditions = array(), \$page = 1, \$show = 100, \$sort = array()) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$collection_name = self::\$collection_name;\n";
	$lin .= "\t\t\t\$ret = array();\n";
	$lin .= "\t\t\t\$offset = \$show * (\$page - 1);\n\n";
	$lin .= "\t\t\t\$offset = is_numeric(\$offset) ? \$offset : false;\n";
	$lin .= "\t\t\t\$show = is_numeric(\$show) ? \$show : false;\n";
	$lin .= "\t\t\tif (\$sort === false || \$offset === false || \$show === false) {\n";
	$lin .= "\t\t\t\treturn \$ret;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\ttry {\n\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->\$collection_name->find(\$conditions)->sort(\$sort)->skip(\$offset)->limit(\$show);\n\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t\t\$site->errorMessage(\"Database error: {\$e->getCode()} in Users::rawWhere()\");\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";


	//function getCurrentUser
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Retrieve the current user\n";
	$lin .= "\t\t * @return mixed User object on success, Null otherwise\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function getCurrentUser() {\n";
	$lin .= "\t\t\t\$ret = self::_getByID( self::\$user_id );\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";


	//function getCurrentUserID
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Retrieve the current user Id\n";
	$lin .= "\t\t * @return integer Current user Id\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function getCurrentUserId() {\n";
	$lin .= "\t\t\treturn self::\$user_id;\n";
	$lin .= "\t\t}\n";



	//function getBy
	$lin .= "\t\t /**\n";
	$lin .= "\t\t * Get an user by the specified field\n";
	$lin .= "\t\t * @param  string \$field Field name: 'login', 'slug' or 'email'\n";
	$lin .= "\t\t * @param  string \$value Value of the field\n";
	$lin .= "\t\t * @return mixed         User object on success, Null otherwise\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function getBy(\$field, \$value) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$ret = null;\n";
	$lin .= "\t\t\t\$collection_name = self::\$collection_name;\n";
	$lin .= "\t\t\t\$fields = array('login', 'slug', 'email');\n";
	$lin .= "\t\t\tif (! in_array(\$field, \$fields) ) {\n";
	$lin .= "\t\t\t\treturn \$ret;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\ttry {\n\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->\$collection_name->findOne(array( \$field => \$value ));\n";
	$lin .= "\t\t\t\tif(\$ret) \$ret = arrayToObject((array)\$ret, 'User');\n\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t\t\$site->errorMessage(\"Database error: {\$e->getCode()} in Users::getBy()\");\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";



	//function checkLogin
	$lin .= "\t\t /**\n";
	$lin .= "\t\t * Recover a previous session\n";
	$lin .= "\t\t * @return boolean True if the user was re-logged in, False otherwise\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function checkLogin() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$name = sprintf('humm_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\$cookie = isset(\$_COOKIE[\$name]) ? \$_COOKIE[\$name] : null;\n";
	$lin .= "\t\t\tif (\$cookie) {\n";
	$lin .= "\t\t\t\t\$id = self::getCookieData(\$cookie);\n";
	$lin .= "\t\t\t\t\$user = self::get(\$id);\n";
	$lin .= "\t\t\t\t# Check user and password\n";
	$lin .= "\t\t\t\tif ( \$user && self::checkCookie(\$cookie) ) {\n";
	$lin .= "\t\t\t\t\t# Save user id\n";
	$lin .= "\t\t\t\t\tself::\$user_id = \$user->_id;\n";
	$lin .= "\t\t\t\t\t\$ret = true;\n";
	$lin .= "\t\t\t\t}\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";
			


	//function requireLogin
	$lin .= "\t\t /**\n";
	$lin .= "\t\t * Check if there's a valid user logged in, otherwise send it to the sign-in page\n";
	$lin .= "\t\t * @return boolean True if the current user is set/valid, otherwise it will be redirected\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tstatic function requireLogin(\$redirect = '/sign-in') {\n";
	$lin .= "\t\t\tglobal \$site;\n\n";
	$lin .= "\t\t\theader(\"Expires: on, 01 Jan 1970 00:00:00 GMT\");\n";
	$lin .= "\t\t\theader(\"Last-Modified: \" . gmdate(\"D, d M Y H:i:s\") . \" GMT\");\n";
	$lin .= "\t\t\theader(\"Cache-Control: no-store, no-cache, must-revalidate\");\n";
	$lin .= "\t\t\theader(\"Cache-Control: post-check=0, pre-check=0\", false);\n";
	$lin .= "\t\t\theader(\"Pragma: no-cache\");\n";
	$lin .= "\t\t\t# Check user\n";
	$lin .= "\t\t\tif ( self::\$user_id ) {\n";
	$lin .= "\t\t\t\treturn true;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\tif (\$redirect) {\n";
	$lin .= "\t\t\t\t\$site->redirectTo( \$site->urlTo(\$redirect) );\n";
	$lin .= "\t\t\t\texit;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn false;\n";
	$lin .= "\t\t}\n\n";



	//fucntion login
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Sign a new user in, replaces previous user (if any)\n";
	$lin .= "\t\t* @param  string  \$user     User name\n";
	$lin .= "\t\t* @param  string  \$password Plain-text password\n";
	$lin .= "\t\t* @param  boolean \$remember Whether to set the cookie for 12 hours (normal) or 2 weeks (remember)\n";
	$lin .= "\t\t* @return boolean           True on success, False otherwise\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function login(\$user, \$password, \$remember = false) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$user = self::getBy('login', \$user);\n";
	$lin .= "\t\t\tif (\$user) {\n";
	$lin .= "\t\t\t\t\$auth = self::checkPassword(\$password, \$user->password);\n";
	$lin .= "\t\t\t\tif (\$auth) {\n";
	$lin .= "\t\t\t\t\t\$expires = strtotime(\$remember ? '+15 day' : '+12 hour');\n";
	$lin .= "\t\t\t\t\t\$cookie = Users::buildCookie(\$expires, \$user->_id);\n";
	$lin .= "\t\t\t\t\t\$name = sprintf('humm_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\t\techo \$name;\n";
	$lin .= "\t\t\t\t\t# Set user id\n";
	$lin .= "\t\t\t\t\tself::\$user_id = \$user->_id;\n";
	$lin .= "\t\t\t\t\t# And set cookie\n";
	$lin .= "\t\t\t\t\t\$ret = setcookie(\$name, \$cookie, \$expires, '/');\n";
	$lin .= "\t\t\t\t\t# Run hooks\n";
	$lin .= "\t\t\t\t\tif (\$user->_id) {\n";
	$lin .= "\t\t\t\t\t\t\$site->executeHook('users.login', \$user->_id);\n";
	$lin .= "\t\t\t\t\t}\n";	
	$lin .= "\t\t\t\t}\n";	
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n\n";
	$lin .= "\t\t}\n\n";



	//function setCurrentUser
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Set the current user\n";
	$lin .= "\t\t* @param integer \$user_id  User ID\n";
	$lin .= "\t\t* @param boolean \$remember Remember user or not\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function setCurrentUser(\$user_id, \$remember = false) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$user = self::get(\$user_id);\n";
	$lin .= "\t\t\tif (\$user) {\n";
	$lin .= "\t\t\t\t\$expires = strtotime(\$remember ? '+15 day' : '+12 hour');\n";
	$lin .= "\t\t\t\t\$cookie = self::buildCookie(\$expires, \$user->_id);\n";
	$lin .= "\t\t\t\t\$name = sprintf('humm_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\t# Set user id\n";
	$lin .= "\t\t\t\tself::\$user_id = \$user->_id;\n";
	$lin .= "\t\t\t\t# And set cookie\n";
	$lin .= "\t\t\t\t\$ret = setcookie(\$name, \$cookie, \$expires, '/');\n";
	$lin .= "\t\t\t\t# Run hooks\n";
	$lin .= "\t\t\t\tif (\$user->_id) {\n";
	$lin .= "\t\t\t\t\t\$site->executeHook('users.login', \$user->_id);\n";
	$lin .= "\t\t\t\t}\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";



	//function switchUser
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Change user, saving the current one\n";
	$lin .= "\t\t* @param  integer \$user_id User ID\n";
	$lin .= "\t\t* @return boolean          True on success, False otherwise\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function switchUser(\$user_id) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$user = self::get(\$user_id);\n";
	$lin .= "\t\t\tif (\$user && \$site->user) {\n";
	$lin .= "\t\t\t\t# Save old user\n";
	$lin .= "\t\t\t\t\$expires = strtotime('+12 hour');\n";
	$lin .= "\t\t\t\t\$cookie = self::buildCookie(\$expires, \$site->user->id);\n";
	$lin .= "\t\t\t\t\$name = sprintf('humm_old_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\t# Set cookie\n";
	$lin .= "\t\t\t\t\$ret = setcookie(\$name, \$cookie, \$expires, '/');\n";
	$lin .= "\t\t\t\t# And now set the new user\n";
	$lin .= "\t\t\t\tself::setCurrentUser(\$user_id);\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";



	//function isUserSwitched
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Check whether the user was switched or not\n";
	$lin .= "\t\t* @return boolean True if the user has been switched\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function isUserSwitched() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$name = sprintf('humm_old_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\$cookie = isset(\$_COOKIE[\$name]) ? \$_COOKIE[\$name] : null;\n";
	$lin .= "\t\t\treturn (\$cookie != null);\n";
	$lin .= "\t\t}\n\n";



	//function restoreUser
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Check whether the user was switched or not\n";
	$lin .= "\t\t* @return boolean True if the user has been switched\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function restoreUser() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$old_name = sprintf('humm_old_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\$old_cookie = isset(\$_COOKIE[\$old_name]) ? \$_COOKIE[\$old_name] : null;\n";
	$lin .= "\t\t\tif (\$old_cookie) {\n";
	$lin .= "\t\t\t\t# Set new user\n";
	$lin .= "\t\t\t\t\$expires = strtotime('+12 hour');\n";
	$lin .= "\t\t\t\t\$name = sprintf('humm_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\t\t# Delete old cookie\n";
	$lin .= "\t\t\t\tsetcookie(\$old_name, '', strtotime('-1 hour'), '/');\n";
	$lin .= "\t\t\t\t# Update cookie\n";
	$lin .= "\t\t\t\t\$ret = setcookie(\$name, \$old_cookie, \$expires, '/');\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";



	//function logOut
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Sign the current user out\n";
	$lin .= "\t\t* @return boolean     True on success, False otherwise\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function logout() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t# Run hooks\n";
	$lin .= "\t\t\tif (self::\$user_id) {\n";
	$lin .= "\t\t\t\t\$site->executeHook('users.logout', self::\$user_id);\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\t# Sign user out\n";
	$lin .= "\t\t\tself::\$user_id = 0;\n";
	$lin .= "\t\t\t\$name = sprintf('humm_login%s', \$site->hashPassword('cookie'));\n";
	$lin .= "\t\t\treturn setcookie(\$name, '', strtotime('-1 hour'), '/');\n";
	$lin .= "\t\t}\n\n";



	//function _doLogout
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Respond to '/logout' route by disconnecting the current user\n";
	$lin .= "\t\t* @return nothing\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function _doLogout() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\tself::logout();\n";
	$lin .= "\t\t\t\$site->redirectTo( \$site->urlTo('/') );\n";
	$lin .= "\t\t\texit;\n";
	$lin .= "\t\t}\n\n";



	//function _getByID
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Helper function, retrieve the user by its ID\n";
	$lin .= "\t\t* @param  integer \$id User ID\n";
	$lin .= "\t\t* @return mixed       User object, False on error\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function _getByID(\$id) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$collection_name = self::\$collection_name;\n";
	$lin .= "\t\t\t\$ret = null;\n";
	$lin .= "\t\t\tif(!\$id) return \$ret;\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->\$collection_name->findOne(array( '_id' => new MongoId(\$id) ));\n";
	$lin .= "\t\t\t\tif(\$ret) \$ret = arrayToObject((array)\$ret, 'User');\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t\t\$site->errorMessage(\"Database error: {\$e->getCode()} in Users::_getByID()\");\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";
	


	//function _getBySlug
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Helper function, retrieve the user by its slug\n";
	$lin .= "\t\t* @param  integer \$slug User slug\n";
	$lin .= "\t\t* @return mixed         User object, False on error\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function _getBySlug(\$slug) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$collection_name = self::\$collection_name;\n";
	$lin .= "\t\t\t\$ret = null;\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$ret = \$dbh->\$collection_name->findOne(array( 'slug' => \$slug ));\n";
	$lin .= "\t\t\t} catch (MongoException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t\t\$site->errorMessage(\"Database error: {\$e->getCode()} in Users::_getBySlug()\");\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";


	//function hashPassword
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Hash a plain-text password\n";
	$lin .= "\t\t* @param  string \$password Plain-text password to hash\n";
	$lin .= "\t\t* @return string           Hashed password\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function hashPassword(\$password) {\n";
	$lin .= "\t\t\t\$hasher = new PasswordHash(8, FALSE);\n";
	$lin .= "\t\t\t\$hash = \$hasher->HashPassword(\$password);\n";
	$lin .= "\t\t\treturn \$hash;\n";
	$lin .= "\t\t}\n\n";



	//function checkPassword
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Check whether the password is valid or not\n";
	$lin .= "\t\t* @param  string \$password    Plain-text password\n";
	$lin .= "\t\t* @param  string \$stored_hash Hashed password, usually from the database\n";
	$lin .= "\t\t* @return boolean             True if the password is valid, False otherwise\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tstatic function checkPassword(\$password, \$stored_hash) {\n";
	$lin .= "\t\t\t\$hasher = new PasswordHash(8, FALSE);\n";
	$lin .= "\t\t\treturn \$hasher->CheckPassword(\$password, \$stored_hash);\n";
	$lin .= "\t\t}\n\n";


	//function builCookie
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Create a hardened cookie\n";
	$lin .= "\t\t* @param  timestamp \$expires When the cookie will expire\n";
	$lin .= "\t\t* @param  string    \$data    Data to save (application state)\n";
	$lin .= "\t\t* @return string             Hardened cookie data\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tprotected static function buildCookie(\$expires, \$data) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t# Get secret key\n";
	$lin .= "\t\t\t\$secret = \$site->hashPassword('hummingbird');\n";
	$lin .= "\t\t\t# Build cookie\n";
	$lin .= "\t\t\t\$cookie = sprintf(\"exp=%s&data=%s\", urlencode(\$expires), urlencode(\$data));\n";
	$lin .= "\t\t\t# Calculate the MAC (message authentication code)\n";
	$lin .= "\t\t\t\$mac = hash_hmac(\"sha256\", \$cookie, \$secret);\n";
	$lin .= "\t\t\t# Append MAC to the cookie and return it\n";
	$lin .= "\t\t\treturn \$cookie . '&digest=' . urlencode(\$mac);\n";
	$lin .= "\t\t}\n\n";



	//function getCookieData
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Get cookie stored data\n";
	$lin .= "\t\t* @param  string \$cookie Cookie data\n";
	$lin .= "\t\t* @return mixed          String with cookie data or False on error\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tprotected static function getCookieData(\$cookie) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t# Get cookie vars\n";
	$lin .= "\t\t\tparse_str(\$cookie, \$vars);\n";
	$lin .= "\t\t\treturn isset( \$vars['data'] ) ? \$vars['data'] : null;\n";
	$lin .= "\t\t}\n\n";
	


	//function checkCookie
	$lin .= "\t\t/**\n";
	$lin .= "\t\t* Check whether the cookie is valid or not\n";
	$lin .= "\t\t* @param  string \$cookie Cookie data\n";
	$lin .= "\t\t* @return boolean        True if the cookie is valid, False otherwise\n";
	$lin .= "\t\t*/\n";
	$lin .= "\t\tprotected static function checkCookie(\$cookie) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t# Get secret key\n";
	$lin .= "\t\t\t\$secret = \$site->hashPassword('hummgingbird');\n";
	$lin .= "\t\t\t# Get cookie vars\n";
	$lin .= "\t\t\tparse_str(\$cookie, \$vars);\n";
	$lin .= "\t\t\tif( empty(\$vars['exp']) || \$vars['exp'] < time() ) {\n";
	$lin .= "\t\t\t\t# Cookie has expired\n";
	$lin .= "\t\t\t\treturn false;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\t# Generate a valid cookie, both should match\n";
	$lin .= "\t\t\t\$str = self::buildCookie(\$vars['exp'], \$vars['data']);\n";
	$lin .= "\t\t\tif (\$str != \$cookie) {\n";
	$lin .= "\t\t\t\t# Cookie has been forged\n";
	$lin .= "\t\t\t\treturn false;\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\t# Otherwise the cookie is valid\n";
	$lin .= "\t\t\treturn true;\n";
	$lin .= "\t\t}\n";

	$lin .= "\t}\n";
	$lin .= "?>";


	$fp = fopen($site->baseDir("/output/{$archivo}"), "w");
	$write = fputs($fp, $lin);
	fclose($fp);

	?>

	<?php $site->getParts(array( 'sticky-footer/header_html', 'sticky-footer/header')) ?>

	<div class="container">
		<div class="margins">
			<div class="alert alert-success">Se generó un archivo llamado <strong><?php echo $archivo; ?></strong> en la carpeta 'output' :D &mdash; <a href="<?php $site->urlTo('/', true); ?>" class="alert-link">Volver a empezar</a></div>
			<p>A continuación se muestra el código generado:</p>
			<pre><?php echo htmlspecialchars($lin); ?></pre>
		</div>
	</div>

	<?php $site->getParts(array( 'sticky-footer/footer', 'sticky-footer/footer_html')) ?>

