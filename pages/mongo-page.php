
<?php $site->getParts(array( 'sticky-footer/header_html', 'sticky-footer/header')) ?>

	<div class="container">
		<div class="margins">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<form action="<?php  $site->urlTo('/mongo_generador',true); ?>" method="post" name="php" class="well" enctype="multipart/form-data">
						<div class="form-group">
							<label class="control-label" for="table">Variables</label>
							<div class="form-group">
								<label class="control-label" for="textarea">Recuerda que esta version espera que el primer dato sea el id</label>
								<textarea class="form-control" rows="4" id="json" name="json"></textarea>
							</div>
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