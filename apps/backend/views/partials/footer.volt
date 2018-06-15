	<!-- Vendor -->
	<script src="/backend/vendor/jquery/jquery.min.js"></script>
	<script src="/backend/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="/backend/vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="/backend/vendor/nanoscroller/nanoscroller.min.js"></script>
	<script src="/backend/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
	<script src="/backend/vendor/magnific-popup/jquery.magnific-popup.min.js"></script>
	<script src="/backend/vendor/jquery-placeholder/jquery-placeholder.min.js"></script>

	<!-- Specific Page Vendor -->
	<script src="/backend/vendor/jquery-ui/jquery-ui.min.js"></script>
	<script src="/backend/vendor/jqueryui-touch-punch/jqueryui-touch-punch.js"></script>
	<script src="/backend/vendor/jquery-appear/jquery-appear.min.js"></script>
	<script src="/backend/vendor/bootstrap-multiselect/bootstrap-multiselect.min.js"></script>
	<script src="/backend/vendor/jquery.easy-pie-chart/jquery.easy-pie-chart.min.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.min.js"></script>
	<script src="/backend/vendor/flot.tooltip/flot.tooltip.min.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.pie.min.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.categories.min.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.resize.min.js"></script>
	<script src="/backend/vendor/jquery-sparkline/jquery-sparkline.min.js"></script>
	<script src="/backend/vendor/raphael/raphael.min.js"></script>
	<script src="/backend/vendor/morris.js/morris.min.js"></script>
	<script src="/backend/vendor/gauge/gauge.min.js"></script>
	<script src="/backend/vendor/snap.svg/snap.svg.js"></script>
	<script src="/backend/vendor/liquid-meter/liquid.meter.min.js"></script>
	<script src="/backend/vendor/jqvmap/jquery.vmap.min.js"></script>
	<script src="/backend/vendor/jqvmap/data/jquery.vmap.sampledata.min.js"></script>
	<script src="/backend/vendor/jqvmap/maps/jquery.vmap.world.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.africa.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.asia.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.australia.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.europe.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js"></script>
	<script src="/backend/vendor/summernote/summernote.min.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="/backend/javascripts/theme.min.js"></script>

	<!-- Theme Custom -->
	<script src="/backend/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="/backend/javascripts/theme.init.min.js"></script>

	{% if current_user %}
	<!-- Examples -->
	<script src="/backend/javascripts/dashboard/examples.dashboard.min.js"></script>
	<script>
		let attachEvent = () => document.querySelectorAll('.notification').forEach(notification => {
			notification.onclick = () => fetch(`/admin/notifications/${notification.dataset.id}/read`, { credentials: 'include', method: 'POST' }).then(() => location.href = notification.dataset.targetUrl)
		});
		attachEvent(),
		setInterval(() => fetch('/admin/home/inbox', { credentials: 'include' }).then(response => response.text()).then(payload => {
			try {
				let response = JSON.parse(payload);
				document.getElementById('inbox').innerHTML = response.data,
				attachEvent()
			} catch (e) {
				return location.href = `/admin/sessions/create?next=${location.pathname}${location.search}`
			}
		}), 6e4),
		$('.summernote').summernote()
	</script>
	{% endif %}
</body>
</html>