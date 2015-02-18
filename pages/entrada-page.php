<?php

	$dbh = $site->getDatabase();
	$db_name = $site->getOption('db_name');
	try {
		#Aqui se convierte en texto
		$sql = "SHOW TABLE STATUS FROM {$db_name}";
		#lo convierte en objeto
		$stmt = $dbh->prepare($sql);
		#lo ejecutas
		$stmt->execute();
		#obtenemos resultados
		$tablas = $stmt->fetchAll();
	} catch (PDOException $e) {
		error_log($e->getMessage());
	}

 ?>
<?php $site->getParts(array( 'sticky-footer/header_html', 'sticky-footer/header')) ?>

	<div class="container">
		<div class="margins">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<form action="<?php  $site->urlTo('/generador',true); ?>" method="post" name="php" class="well" enctype="multipart/form-data">
						<div class="form-group">
							<label class="control-label" for="table">Tabla</label>
							<select class="form-control" name="table">
								<?php foreach ($tablas as $tabla): ?>
									<option value="<?php echo $tabla->Name; ?>"><?php echo $tabla->Name; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label" for="dato1">Singular</label>
							<input type="text" class="form-control" id="dato1" name="dato1" data-validate="required">
						</div>
						<div class="form-group">
							<label class="control-label" for="dato2">Plural</label>
							<input type="text" class="form-control" id="dato2" name="dato2" data-validate="required<a href=">
						</div>
						<div class="text-right">
							<button type="submit" class="btn btn-success">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

<?php $site->getParts(array( 'sticky-footer/footer', 'sticky-footer/footer_html')) ?>