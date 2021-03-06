<pre>

<h1>Setting up database schema</h1>

<? include_once('config.php');

function execute_checkup ($error_info, $message) {
	if ($error_info[0] == "0000"): echo "Succcess ".$message."<hr>";
	else: echo "Failure ".$message.".<br>".$error_info[2]."<hr>"; footer(); endif; }

function random_code($length=16) {
	$characters = [
		"2", "3", "4", "5", "6", "7",
//		"Q", "W", "E", "R". "T", "Y", "U", "I", "O", "P", 
		"Q", "W", "R". "T", "Y", "P", // remove vowels
//		"A", "S", "D", "F", "G", "H", "J", "K", "L", 
		"S", "D", "F", "G", "H", "J", "K", "L", // remove vowels
//		"Z", "X", "C", "V", "B", "N", "M"
		"Z", "C", "V", "B", "N", "M" // remove 'x' for vulgar use
		];
	if (!(is_int($length))): $length = 16; endif;
	if ($length < 1): $length = 16; endif;
	$key_temp = null;
	while (strlen($key_temp) < $length): $key_temp .= $characters[rand(0,31)]; endwhile;
	return $key_temp; }

// make connection without database
$connection_pdo = new PDO(
	"mysql:host=$server;charset=utf8mb4", 
	$username, 
	$password,
	array(
		PDO::ATTR_TIMEOUT => 3, // in seconds
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		)
	);


// create database
$sql_temp = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
$run_statement = $connection_pdo->prepare($sql_temp);
$run_statement->execute();
execute_checkup($run_statement->errorInfo(), "creating database $database");

// make connection with database now that it certainly exists
$connection_pdo = new PDO(
	"mysql:host=$server;dbname=$database;charset=utf8mb4", 
	$username, 
	$password,
	array(
		PDO::ATTR_TIMEOUT => 3, // in seconds
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		)
	);

// create entries directory table
$tables_array = [
	"information_directory" =>
		[
		"`entry_id` VARCHAR(10)",
		"`type` VARCHAR(20)",
		"`date_published` DATE", // Add this column
		"`date_updated` DATETIME", // And add this column
		"`name` VARCHAR(1000)",
//		"`alternate_name` VARCHAR(500)",
		"`summary` TEXT",
		"`body` LONGTEXT",
		"`studies` LONGTEXT",
		"`appendix` TEXT",
		"`timestamp` TIMESTAMP",
		"PRIMARY KEY (`entry_id`)",
		],
	"information_paths" =>
		[
		"`path_id` VARCHAR(100)",
		"`parent_id` VARCHAR(100)",
		"`path_type` VARCHAR(100)",
		"`child_id` VARCHAR(100)",
		"`timestamp` TIMESTAMP",
		"PRIMARY KEY (`path_id`)",
		],
	"information_history" =>
		[
		"`information_id` VARCHAR(10)",
		"`entry_id` VARCHAR(10)",
		"`information_name` VARCHAR(100)",
		"`information_value` TEXT",
		"`timestamp` TIMESTAMP",
		"PRIMARY KEY (`information_id`)",
		],
	"siteinfo" =>
		[
		"`key` VARCHAR(100)",
		"`value` TEXT",
		"timestamp TIMESTAMP",
		"PRIMARY KEY (`key`)",
		],
	"locations_shapes" =>
		[
		"`line_id` VARCHAR(10)",
		"`shape_id` VARCHAR(10)",
		"`entry_id` VARCHAR(10)",
		"`start_latitude` DECIMAL(16,13)",
		"`start_longitude` DECIMAL(16,13)",
		"`end_latitude` DECIMAL(16,13)",
		"`end_longitude` DECIMAL(16,13)",
		"`timestamp` TIMESTAMP",
		"PRIMARY KEY (`line_id`)",
		],
	"users" =>
		[
		"`user_id` VARCHAR(100)",
		"`status` VARCHAR(100)",
		"`email` VARCHAR(100)",
		"`name` VARCHAR(100)",
		"`hash` VARCHAR(400)",
		"`authenticator` VARCHAR(100)",
		"`cookie` VARCHAR(100)",
		"timestamp TIMESTAMP",
		"PRIMARY KEY (`user_id`)",	
		],
	];

foreach ($tables_array as $table_name => $columns_info):
	$sql_temp = "CREATE TABLE IF NOT EXISTS ".$database.".".$table_name." (".implode(", ", $columns_info).") DEFAULT CHARSET=utf8mb4";
	$run_statement = $connection_pdo->prepare($sql_temp);
	$run_statement->execute();
	execute_checkup($run_statement->errorInfo(), "creating ".$table_name." table");
	endforeach;

// Format for adding columns, e.g. during an upgrade
$columns_array = [
	
//	[
//	"table"		=> "information_directory",
//	"column"	=> "`date_published` DATE",
//	"after"		=> "`entry_id`",
//	],
	
//	[
//	"table"		=> "information_directory",
//	"column"	=> "`body` LONGTEXT",
//	],
	
//	[
//	"table"		=> "information_directory",
//	"column"	=> "`studies` LONGTEXT",
//	],
	
	];

foreach ($columns_array as $column_info):

	// Add in new columns
//	$sql_temp = "ALTER TABLE ".$database.".".$column_info['table']." ADD COLUMN ".$column_info['column']." AFTER ".$column_info['after'];
//	$run_statement = $connection_pdo->prepare($sql_temp);
//	$run_statement->execute();
//	execute_checkup($run_statement->errorInfo(), "adding ".$column_info['column']." to ".$column_info['table']);

	// Alter column types; this is redundant with the add, intended for something already added, but needs to be modified
//	$sql_temp = "ALTER TABLE ".$database.".".$column_info['table']." MODIFY COLUMN ".$column_info['column'];
//	$run_statement = $connection_pdo->prepare($sql_temp);
//	$run_statement->execute();
//	execute_checkup($run_statement->errorInfo(), "modifying ".$column_info['column']." in ".$column_info['table']);

	endforeach;

if (!(empty($_POST['submit']))):

	if (empty($_POST['email']) || empty($_POST['password1'])):
		echo "<b style='color: red;'>User information incomplete</b><hr>";
	elseif ($_POST['password1'] == $_POST['password2']):

		// Prepare statement
		$sql_temp = "INSERT INTO $database.users (`user_id`, `email`, `hash`) VALUES (:user_id, :email, :hash) ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`), `email`=VALUES(`email`), `hash`=VALUES(`hash`)";
		$run_statement = $connection_pdo->prepare($sql_temp);

		// Set up values
		$values_temp = [
			"user_id"=>random_code(10),
			"email"=>$_POST['email'],
			"hash"=>sha1($_POST['email'].$_POST['password1']) ];

		// Execute and check
		$run_statement->execute($values_temp);
		execute_checkup($run_statement->errorInfo(), "creating account login");

	else:
		echo "<b style='color: red;'>Passwords did not match</b><hr>";
		endif;
	endif;

// select users from table and if it is empty then create a user
$sql_temp = "SELECT * FROM $database.users";
$login = 0;
foreach ($connection_pdo->query($sql_temp) as $row):
	$login = 1;
	endforeach;

if ($login == 0):
	echo "<hr>";
	echo "<h1>Create login</h1>";
	echo "<form action='' method='post'>";
	echo "<input type='email' name='email' placeholder='E-mail' required><br>";
	echo "<input type='password' name='password1' placeholder='Password' required><br>";
	echo "<input type='password' name='password2' placeholder='Password (confirm)' required><br>";
	echo "<input type='submit' name='submit' value='create'>";
	echo "</form>";
	exit; endif; ?>

<h1>Configure Apache</h1>

1) Run this command: <i>sudo a2enmod rewrite</i>
2) Locate your Apache config file, usually in /etc/apache2/sites-available/
3) Update your Apache config file by adding the follow chunk at the bottom outside virtualhosts,
<i>&lt;Directory /var/www/[DIRECTORY]/&gt;
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
&lt;/Directory&gt;</i>
4) Run this command to restart Apache: <i>sudo service apache2 restart</i>

</pre>
