	<!-- Vendor -->
	<script src="/backend/vendor/jquery/jquery.js"></script>
	<script src="/backend/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="/backend/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="/backend/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="/backend/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="/backend/vendor/magnific-popup/jquery.magnific-popup.js"></script>
	<script src="/backend/vendor/jquery-placeholder/jquery-placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="/backend/vendor/jquery-ui/jquery-ui.js"></script>
	<script src="/backend/vendor/jqueryui-touch-punch/jqueryui-touch-punch.js"></script>
	<script src="/backend/vendor/jquery-appear/jquery-appear.js"></script>
	<script src="/backend/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
	<script src="/backend/vendor/jquery.easy-pie-chart/jquery.easy-pie-chart.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.js"></script>
	<script src="/backend/vendor/flot.tooltip/flot.tooltip.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.pie.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.categories.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.resize.js"></script>
	<script src="/backend/vendor/jquery-sparkline/jquery-sparkline.js"></script>
	<script src="/backend/vendor/raphael/raphael.js"></script>
	<script src="/backend/vendor/morris.js/morris.js"></script>
	<script src="/backend/vendor/gauge/gauge.js"></script>
	<script src="/backend/vendor/snap.svg/snap.svg.js"></script>
	<script src="/backend/vendor/liquid-meter/liquid.meter.js"></script>
	<script src="/backend/vendor/jqvmap/jquery.vmap.js"></script>
	<script src="/backend/vendor/jqvmap/data/jquery.vmap.sampledata.js"></script>
	<script src="/backend/vendor/jqvmap/maps/jquery.vmap.world.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.africa.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.asia.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.australia.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.europe.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js"></script>
	<script src="/backend/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js"></script>
	<script src="/backend/vendor/summernote/summernote.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="/backend/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="/backend/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="/backend/javascripts/theme.init.js"></script>

	{% if current_user %}
	<!-- Examples -->
	<script src="/backend/javascripts/dashboard/examples.dashboard.js"></script>
	<script>
		setInterval(() => {
			fetch('/admin/home/inbox', { credentials: 'include' }).then(response => {
				return response.text()
			}).then(payload => {
				document.getElementById('inbox').innerHTML = payload;
				for (let notifications = document.querySelectorAll('.notification'), i = notifications.length; i--; ) {
					notifications[i].onclick = () => {
						fetch('/admin/notifications/update/' + this.dataset.id + '/read:1', { credentials: 'include', method: 'POST' }).then(() => {
							location.href = this.dataset.link
						})
					}
				}
			})
		}, 60000),
		$('.summernote').summernote()
	</script>
	{% endif %}
</body>
</html>