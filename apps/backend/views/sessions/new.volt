<!-- start: page -->
<section class="body-sign">
	<div class="center-sign">
		<a href="/admin" class="logo pull-left">
			<img src="/backend/images/logo.png" height="54" alt="Ikoma-Home Admin">
		</a>
		<div class="panel panel-sign">
			<div class="panel-title-sign mt-xl text-right">
				<h2 class="title text-uppercase text-weight-bold m-none"><i class="fa fa-user mr-xs"></i> Login Administrator</h2>
			</div>
			<div class="panel-body">
				{{ flashSession.output() }}
				{{ form('/admin/sessions/create', 'method': 'POST') }}
					<input type="hidden" name="{{ token_key }}" value="{{ token }}">
					<div class="form-group mb-lg">
						<label>Username</label>
						<div class="input-group input-group-icon">
							{{ text_field('username', 'class': 'form-control input-lg') }}
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
							{{ password_field('password', 'value': '', 'class': 'form-control input-lg') }}
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
							{{ submit_button('LOGIN', 'class': 'btn btn-primary hidden-xs') }}
						</div>
					</div>
				</form>
				<br>
			</div>
		</div>
		<p class="text-center text-muted mt-md mb-md">&copy; Copyright 2015. All Rights Reserved.</p>
	</div>
</section>
<!-- end: page -->