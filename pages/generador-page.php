<?php

	$singular = $_POST["dato1"];
	$plural = $_POST["dato2"];
	$table = $_POST["table"];

	$dbh = $site->getDatabase();
	try {
		$schema = $site->getOption('db_name');
		// $sql = "SELECT column_name FROM information_schema.columns WHERE table_name='{$table}'";
		$sql = "DESCRIBE {$schema}.{$table}";
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$columnas = $stmt->fetchAll();
	} catch (PDOException $e) {
		error_log($e->getMessage());
	}

	$archivo = strtolower($singular) . ".model.php";
	$singular = ucfirst($singular);
	$plural = ucfirst($plural);
	$lin = "";
	$lin .= "<?php \n\n ";
	$lin .= "\tclass {$singular}";
	$lin .= " ";
	$lin .= "extends ";
	$lin .= "Model {\n\n";

	foreach ($columnas as $columna) {
		$lin .= "\t\tpublic \${$columna->Field};\n";
	}

	$lin .= "\n\t\t/**\n\t\t * Initialization callback\n";
	$lin .= "\t\t";
	$lin .= " * @return nothing\n";
	$lin .= "\t\t */\n";
	$lin .= "\t\tfunction init() {\n";
	$lin .= "\t\t\tif (! \$this->id ) {\n";
	$lin .= "\t\t\t\t\$now = date('Y-m-d H:i:s');\n";
	foreach ($columnas as $columna) {
		$lin .= "\t\t\t\t\$this->{$columna->Field} = ".(preg_match('/(varchar|text)/', $columna->Type) === 1 ? "''" : '0').";\n";
	}
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t}\n";
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Save model\n";
	$lin .= "\t\t * @return boolean True on success, False otherwise\n";
	$lin .= "\t\t */\n";

	#funcion save
	$lin .= "\t\tfunction save() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$this->modified = date('Y-m-d H:i:s');\n";
	$lin .= "\t\t\ttry {\n";

	#INSERT INTO
	$lin .= "\t\t\t\t\$sql = \"INSERT INTO {$table} (";
	foreach ($columnas as $columna) {
		$lin .= "{$columna->Field}, ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= ")\n";


	#VALUES
	$lin .= "\t\t\t\t\t\tVALUES (";
	foreach ($columnas as $columna) {
		$lin .= ":{$columna->Field}, ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= ")\n";

	#ON
	$lin .= "\t\t\t\t\t\tON DUPLICATE KEY UPDATE ";
	foreach ($columnas as $columna) {
		$lin .= "{$columna->Field} = :{$columna->Field}, ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= "\";\n";

	$lin .= "\t\t\t\t\$stmt = \$dbh->prepare(\$sql);\n";
	foreach ($columnas as $columna) {
		$lin .= "\t\t\t\t\$stmt->bindValue(':{$columna->Field}', \$this->{$columna->Field});\n";
	}
	$lin .= "\t\t\t\t\$stmt->execute();\n";
	$lin .= "\t\t\t\tif (! \$this->id && \$dbh->lastInsertId() ) {\n";
	$lin .= "\t\t\t\t\t\$this->id = \$dbh->lastInsertId();\n";
	$lin .= "\t\t\t\t}\n";
	$lin .= "\t\t\t\t\$ret = true;\n";

	$lin .= "\t\t\t} catch (PDOException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Delete model\n";
	$lin .= "\t\t * @return boolean True on success, False otherwise\n";
	$lin .= "\t\t */\n";

	#function delete
	$lin .= "\t\tfunction delete() {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$this->modified = date('Y-m-d H:i:s');\n";
	$lin .= "\t\t\ttry {\n";

	$lin .= "\t\t\t\t\$sql = \"DELETE FROM {$table} WHERE id = :id\";\n";
	$lin .= "\t\t\t\t\$stmt = \$dbh->prepare(\$sql);\n";
	$lin .= "\t\t\t\t\$stmt->bindValue(':id', \$this->id);\n";
	$lin .= "\t\t\t\t\$stmt->execute();\n";
	$lin .= "\t\t\t\t\$ret = true;\n";

	$lin .= "\t\t\t} catch (PDOException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";

	$lin .= "\t\t}\n\n";

	#function __toString
	$lin .= "\t\tfunction __toString() {\n";
	$lin .= "\t\t\treturn json_encode(\$this);\n";
	$lin .= "\t\t}\n\n";

	$lin .= "\t}\n\n";
	#
	#Aqui emepzamos la clase en plural
	$lin .= "\tclass {$plural} {\n\n";
	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Get an item\n";
	$lin .= "\t\t * @param Integer \$id ID of the item to retrieve\n";
	$lin .= "\t\t */\n";

	#fuction get
	$lin .= "\t\tstatic function get(\$id) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$sql = \"SELECT ";
	foreach ($columnas as $columna) {
		$lin .= "{$columna->Field}, ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= " FROM {$table} WHERE id = :id\";\n";
	$lin .= "\t\t\t\t\$stmt = \$dbh->prepare(\$sql);\n";
	$lin .= "\t\t\t\t\$stmt->bindValue(':id', \$id);\n";
	$lin .= "\t\t\t\t\$stmt->execute();\n";
	$lin .= "\t\t\t\t\$stmt->setFetchMode(PDO::FETCH_CLASS, '{$singular}');\n";
	$lin .= "\t\t\t\t\$ret = \$stmt->fetch();\n";
	$lin .= "\t\t\t} catch (PDOException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n";

	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Get all items\n";
	$lin .= "\t\t * @param integer \$offset How much ites to skip\n";
	$lin .= "\t\t * @param integer \$limit  How much ites to retrieve\n";
	$lin .= "\t\t * @param string  \$order  Colum for ordering\n";
	$lin .= "\t\t * @param string  \$sort   Sort order (ASC, DESC)\n";
	$lin .= "\t\t * @param mixed           Array with fetched objects or False on error\n";
	$lin .= "\t\t */\n";

	# static function "all"
	$lin .= "\t\tstatic function all(\$offset = 0, \$limit = 1000, \$order = 'id', \$sort = 'DESC') {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$offset = is_numeric(\$offset) ? \$offset : 0;\n";
	$lin .= "\t\t\t\$limit = is_numeric(\$limit) ? \$limit : 1000;\n";
	$lin .= "\t\t\t\$sort = in_array(strtoupper(\$sort), array('ASC', 'DESC')) ? \$sort : 'DESC';\n";
	$lin .= "\t\t\t\$order = in_array(strtolower(\$order), array(";
	foreach ($columnas as $columna) {
		$lin .= "'{$columna->Field}', ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= ")) ? \$order : 'id';\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$sql = \"SELECT ";
	foreach ($columnas as $columna) {
		$lin .= "{$columna->Field}, ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= " FROM {$table}";
	$lin .= " ORDER BY {\$order} {\$sort} LIMIT {\$offset},{\$limit}\";\n";
	$lin .= "\t\t\t\t\$stmt = \$dbh->prepare(\$sql);\n";
	$lin .= "\t\t\t\t\$stmt->execute();\n";
	$lin .= "\t\t\t\t\$stmt->setFetchMode(PDO::FETCH_CLASS, '{$singular}');\n";
	$lin .= "\t\t\t\t\$ret = \$stmt->fetchAll();\n";

	$lin .= "\t\t\t} catch (PDOException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";

	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Get all items that match a condition\n";
	$lin .= "\t\t * @param  string  \$field    Which column to check\n";
	$lin .= "\t\t * @param  mixed   \$value    Which value to compare\n";
	$lin .= "\t\t * @param  string  \$operator SQL comparison operator (=, >, <, LIKE, IN, etc)\n";
	$lin .= "\t\t * @param  integer \$offset   How much items to skip\n";
	$lin .= "\t\t * @param  integer \$limit    How much items to retrieve\n";
	$lin .= "\t\t * @param  string  \$order    Column for ordering\n";
	$lin .= "\t\t * @param  string  \$sort     Sort order (ASC, DESC)\n";
	$lin .= "\t\t * @return mixed             Array with fetched objects or False on error\n";
	$lin .= "\t\t */\n";

	#static function where
	$lin .= "\t\tstatic function where(\$field, \$value, \$operator = '=', \$offset = 0, \$limit = 1000, \$order = 'id', \$sort = 'DESC') {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$field = in_array(strtolower(\$field), array(";
	foreach ($columnas as $columna) {
		$lin .= "'{$columna->Field}', ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= ")) ? \$field : 'id';\n";
	$lin .= "\t\t\t\$value = is_numeric(\$value) ? \$value : \$dbh->quote(\$value);\n";
	$lin .= "\t\t\treturn self::rawWhere(\"{\$field} {\$operator} {\$value}\", \$offset, \$limit, \$order, \$sort);\n" ;
	$lin .= "\t\t}\n\n";

	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Get all items that match some conditions\n";
	$lin .= "\t\t * @param  string  \$conditions A valid, well-formed SQL set of WHERE conditions (without the WHERE keyword itself)\n";
	$lin .= "\t\t * @param  integer \$offset     How much items to skip\n";
	$lin .= "\t\t * @param  integer \$limit      How much items to retrieve\n";
	$lin .= "\t\t * @param  string  \$order      Column for ordering\n";
	$lin .= "\t\t * @param  string  \$sort       Sort order (ASC, DESC)\n";
	$lin .= "\t\t * @return mixed               Array with fetched objects or False on error)\n";
	$lin .= "\t\t */\n";

	#static function rawWhere
	$lin .= "\t\tstatic function rawWhere(\$conditions, \$offset = 0, \$limit = 1000, \$order = 'id', \$sort = 'DESC') {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\t\$offset = is_numeric(\$offset) ? \$offset : 0;\n";
	$lin .= "\t\t\t\$limit = is_numeric(\$limit) ? \$limit : 1000;\n";
	$lin .= "\t\t\t\$sort = in_array(strtoupper(\$sort), array('ASC', 'DESC')) ? \$sort : 'DESC';\n";
	$lin .= "\t\t\t\$order = in_array(strtolower(\$order), array(";
	foreach ($columnas as $columna) {
		$lin .= "'{$columna->Field}', ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= ")) ? \$order : 'id';\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$sql = \"SELECT ";
	foreach ($columnas as $columna) {
		$lin .= "{$columna->Field}, ";
	}
	$lin = rtrim($lin, ", ");
	$lin .= " FROM {$table} WHERE {\$conditions} ORDER BY {\$order} {\$sort} LIMIT {\$offset},{\$limit}\";\n";
	$lin .= "\t\t\t\t\$stmt = \$dbh->prepare(\$sql);\n";
	$lin .= "\t\t\t\t\$stmt->execute();\n";
	$lin .= "\t\t\t\t\$stmt->setFetchMode(PDO::FETCH_CLASS, '{$singular}');\n";
	$lin .= "\t\t\t\t\$ret = \$stmt->fetchAll();\n";
	$lin .= "\t\t\t} catch (PDOException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n\n";

	$lin .= "\t\t/**\n";
	$lin .= "\t\t * Get the amount of items that match certain conditions\n";
	$lin .= "\t\t * @param  string  \$conditions A valid, well-formed SQL set of WHERE conditions (without the WHERE keyword itself)\n";
	$lin .= "\t\t * @return integer              Number of elements that match the conditions\n";
	$lin .= "\t\t */\n";

	#static funcction count
	$lin .= "\t\tstatic function count(\$conditions = 1) {\n";
	$lin .= "\t\t\tglobal \$site;\n";
	$lin .= "\t\t\t\$ret = false;\n";
	$lin .= "\t\t\t\$dbh = \$site->getDatabase();\n";
	$lin .= "\t\t\ttry {\n";
	$lin .= "\t\t\t\t\$sql = \"SELECT COUNT(id) AS total FROM {$table} WHERE {\$conditions}\";\n";
	$lin .= "\t\t\t\t\$stmt = \$dbh->prepare(\$sql);\n";
	$lin .= "\t\t\t\t\$stmt->execute();\n";
	$lin .= "\t\t\t\t\$row = \$stmt->fetch();\n";
	$lin .= "\t\t\t\tif (\$row) {\n";
	$lin .= "\t\t\t\t\t\$ret = \$row->total;\n";
	$lin .= "\t\t\t\t}\n";
	$lin .= "\t\t\t} catch (PDOException \$e) {\n";
	$lin .= "\t\t\t\terror_log( \$e->getMessage() );\n";
	$lin .= "\t\t\t}\n";
	$lin .= "\t\t\treturn \$ret;\n";
	$lin .= "\t\t}\n";
	$lin .= "\t}\n";

	$lin .= "?>" ;

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
