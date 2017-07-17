<section class="body">
	<!-- start: header -->
	{{ partial('partials/top_menu') }}
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		{{ partial('partials/left_side') }}
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<a href="/password/create"><h2>My Account</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span>My Account</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">My Account</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="POST" action="/password/create">
					<div class="form-group">
						<label class="col-md-3 control-label" for="name">Nama</label>
						<div class="col-md-6">
							<div class="input-group input-group-icon">
								<span class="input-group-addon">
									<span class="icon"><i class="fa fa-user"></i></span>
								</span>
								<input type="text" class="form-control" name="name" value="{{ current_user.name }}" id="name" disabled>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label" for="password">Password Lama</label>
						<div class="col-md-6">
							<div class="input-group input-group-icon">
								<span class="input-group-addon">
									<span class="icon"><i class="fa fa-key"></i></span>
								</span>
								<input class="form-control" type="password" name="password" id="password" placeholder="Password Lama">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label" for="new_password">Password Baru</label>
						<div class="col-md-6">
							<div class="input-group input-group-icon">
								<span class="input-group-addon">
									<span class="icon"><i class="fa fa-key"></i></span>
								</span>
								<input class="form-control" type="password" name="new_password" id="new_password" placeholder="Password Baru">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label" for="new_password_confirmation">Ulangi Password Baru</label>
						<div class="col-md-6">
							<div class="input-group input-group-icon">
								<span class="input-group-addon">
									<span class="icon"><i class="fa fa-key"></i></span>
								</span>
								<input class="form-control" type="password" name="new_password_confirmation" id="new_password_confirmation" placeholder="Ulangi Password Baru">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">&nbsp;</label>
						<div class="col-md-6">
							<button class="btn btn-primary">SIMPAN</button>
						</div>
					</div>
				</form>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>