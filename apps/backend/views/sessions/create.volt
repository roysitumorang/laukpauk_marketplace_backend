<!-- start: page -->
<section class="body-sign">
	<div class="center-sign">
		<a href="/admin" class="logo pull-left">
			<img src="/backend/images/logo.png" height="54" alt="LaukPauk.id-Home Admin">
		</a>
		<div class="panel panel-sign">
			<div class="panel-title-sign mt-xl text-right">
				<h2 class="title text-uppercase text-weight-bold m-none"><i class="fa fa-user mr-xs"></i> Login Administrator</h2>
			</div>
			<div class="panel-body">
				{{ flashSession.output() }}
				<form method="POST" action="/admin/sessions/create{% if next_url %}?next={{ next_url }}{% endif %}">
					<input type="hidden" name="{{ token_key }}" value="{{ token }}">
					<div class="form-group mb-lg">
						<label>Email</label>
						<div class="input-group input-group-icon">
							<input type="text" name="email" value="{{ email }}" class="form-control input-lg">
							<span class="input-group-addon">
								<span class="icon icon-lg">
									<i class="fa fa-user"></i>
								</span>
							</span>
						</div>
					</div>
					<div class="form-group mb-lg">
						<div class="clearfix">
							<label class="pull-left">Password</label>
						</div>
						<div class="input-group input-group-icon">
							<input type="password" name="password" class="form-control input-lg">
							<span class="input-group-addon">
								<span class="icon icon-lg">
									<i class="fa fa-lock"></i>
								</span>
							</span>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-8"></div>
						<div class="col-sm-4 text-right">
							<button type="submit" class="btn btn-primary hidden-xs">LOGIN</button>
						</div>
					</div>
				</form>
				<br>
			</div>
		</div>
		<p class="text-center text-muted mt-md mb-md">&copy; Copyright 2016. All Rights Reserved.</p>
	</div>
</section>
<!-- end: page -->