<header class="header">
	<div class="logo-container">
		<a href="/admin" class="logo">
			<img src="/backend/images/logo.png" height="35" alt="Ikoma-Home Admin">
		</a>
		<div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
			<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>
	<!-- start: search & user box -->
	<div class="header-right">
		<form action="/admin/products" class="search nav-form" method="GET">
			<div class="input-group input-search">
				<input type="text" name="keyword" id="keyword" value="{{ product_keyword }}" placeholder="Cari Produk" class="form-control">
				<span class="input-group-btn">
					<button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
				</span>
			</div>
		</form>
		<span class="separator"></span>
		<span id="loadInbox">
		<ul class="notifications">
			<li>
				<a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
					<i class="fa fa-envelope"></i>
					{% if count(unread_messages) %}
					<span class="badge">{{ count(unread_messages) }}</span>
					{% endif %}
				</a>
				<div class="dropdown-menu notification-menu">
					<div class="notification-title">
						<span class="pull-right label label-default">{{ count(unread_messages) }}</span>
						Messages
					</div>
					<div class="content">
						<ul>
						{% for message in unread_messages %}
							<li>
								<a href="/admin/messages/show/{{ message.id }}" class="clearfix">
									<span class="title">{{ message.subject }}</span>
									<span class="message truncate">{{ message.body }}</span>
								</a>
							</li>
						{% elsefor %}
							<li>
								<a href="/admin/messages" class="clearfix">
									<span class="title">Maaf</span>
									<span class="message">Belum ada pesan baru</span>
								</a>
							</li>
						{% endfor %}
						</ul>
						<hr>
						<div class="text-right">
							<a href="/admin/messages" class="view-more">Tampilkan Semua</a>
						</div>
					</div>
				</div>
			</li>
			<li>
				<a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
					<i class="fa fa-bell"></i>
					{% if count(unread_notifications) %}
					<span class="badge">{{ count(unread_notifications) }}</span>
					{% endif %}
				</a>
				<div class="dropdown-menu notification-menu">
					<div class="notification-title">
						<span class="pull-right label label-default">{{ count(unread_notifications) }}</span>
						Notifikasi
					</div>
					<div class="content">
						<ul>
						{% for notification in unread_notifications %}
							<li>
								<a href="{{ notification.link }}" class="clearfix notification" data-id="{{ notification.id }}">
									<span class="title">{{ notification.created_at }}</span>
									<span class="message">{{ notification.subject }}</span>
								</a>
							</li>
						{% elsefor %}
							<li>
								<a href="/admin/notifications" class="clearfix">
									<span class="title">Maaf</span>
									<span class="message">Belum ada notifikasi</span>
								</a>
							</li>
						{% endfor %}
						</ul>
						<hr>
						<div class="text-right">
							<a href="/admin/notifications" class="view-more">Tampilkan Semua</a>
						</div>
					</div>
				</div>
			</li>
		</ul>
		</span>
		<span class="separator"></span>
		<div id="userbox" class="userbox">
			<a href="#" data-toggle="dropdown">
				<figure class="profile-picture">
					<img src="/backend/images/!logged-user.jpg" alt="{{ current_user.name }}" class="img-circle" data-lock-picture="/backend/images/!logged-user.jpg">
				</figure>
				<div class="profile-info" data-lock-name="{{ current_user.name }}" data-lock-email="{{ current_user.email }}">
					<span class="name">{{ current_user.name }}</span>
					<span class="role">{{ current_user.user_type }}</span>
				</div>
				<i class="fa custom-caret"></i>
			</a>
			<div class="dropdown-menu">
				<ul class="list-unstyled">
					<li class="divider"></li>
					<li>
						<a role="menuitem" tabindex="-1" href="/admins/passwords"><i class="fa fa-user"></i> My Account</a>
					</li>
					<li>
						<a role="menuitem" tabindex="-1" href="/admin/sessions/delete"><i class="fa fa-power-off"></i> Logout</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<!-- end: search & user box -->
</header>
<script>
	for (let notifications = document.querySelectorAll('.notification'), i = notifications.length; i--; ) {
		notifications[i].onclick = function() {
			fetch('/admin/notifications/update/' + this.dataset.id + '/read:1', {method: 'POST'})
		}
	}
</script>