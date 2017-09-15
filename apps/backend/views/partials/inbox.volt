<ul class="notifications">
	<li>
		<a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
			<i class="fa fa-envelope"></i>
			{% if count(unread_messages) %}
			<span class="badge">{{ unread_messages | count }}</span>
			{% endif %}
		</a>
		<div class="dropdown-menu notification-menu">
			<div class="notification-title">
				<span class="pull-right label label-default">{{ unread_messages | count }}</span>
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
			<span class="badge">{{ unread_notifications | count }}</span>
			{% endif %}
		</a>
		<div class="dropdown-menu notification-menu">
			<div class="notification-title">
				<span class="pull-right label label-default">{{ unread_notifications | count }}</span>
				Notifikasi
			</div>
			<div class="content">
				<ul>
				{% for notification in unread_notifications %}
					<li>
						<a href="javascript:void(0)" data-id="{{ notification.id }}" data-target-url="{{ notification.admin_target_url }}" class="clearfix notification">
							<span class="title">{{ notification.created_at }}</span>
							<span class="message">{{ notification.title }}</span>
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
