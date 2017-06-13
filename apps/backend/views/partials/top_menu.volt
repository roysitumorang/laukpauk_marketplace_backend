<header class="header">
	<div class="logo-container">
		<a href="/admin" class="logo">
			<img src="/backend/images/logo.png" height="35" alt="Laukpauk.id Admin">
		</a>
		<div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
			<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>
	<!-- start: search & user box -->
	<div class="header-right">
		<span id="inbox">
			{{ partial('partials/inbox', ['unread_notifications': unread_notifications, 'unread_messages': unread_messages]) }}
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
						<a role="menuitem" tabindex="-1" href="/admin/password/create"><i class="fa fa-user"></i> My Account</a>
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