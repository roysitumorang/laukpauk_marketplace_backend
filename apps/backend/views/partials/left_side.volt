<aside id="sidebar-left" class="sidebar-left">
	<div class="sidebar-header">
		<div class="sidebar-title">
			Navigation
		</div>
		<div class="sidebar-toggle hidden-xs" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
			<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>
	<div class="nano">
		<div class="nano-content">
			<nav id="menu" class="nav-main" role="navigation">
				<ul class="nav nav-main">
					{% for item in menu %}
					<li{% if item['sub_items'] %} class="nav-parent{% if item['expanded'] %} nav-expanded{% endif %}"{% endif %}>
						<a{% if item['link'] %} href="/admin/{{ item['link'] }}"{% endif %}>
							<i class="fa fa-{{ item['icon'] }}" aria-hidden="true"></i>
							<span>{{ item['label'] }}</span>
						</a>
						{% if item['sub_items'] %}
						<ul class="nav nav-children">
							{% for sub_item in item['sub_items'] %}
							<li><a href="/admin/{{ sub_item['link'] }}">{{ sub_item['label'] }}</a></li>
							{% endfor %}
						</ul>
						{% endif %}
					</li>
					{% endfor %}
				</ul>
			</nav>
			<hr class="separator">
		</div>
	</div>
</aside>