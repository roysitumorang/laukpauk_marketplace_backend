<ul class="nav nav-tabs">
	<li{% if expand == 'users' %} class="active"{% endif %}><a href="/users">Daftar Member</a></li>
	<li{% if expand == 'create_user' %} class="active"{% endif %}><a href="/users/create">Tambah Member</a></li>
	{% if user.id %}
		<li{% if expand == 'show_user' %} class="active"{% endif %}><a href="/users/{{ user.id }}">Profil</a></li>
		<li{% if expand == 'update_user' %} class="active"{% endif %}><a href="/users/{{ user.id }}/update">Edit Profil</a></li>
	{% endif %}
</ul>
