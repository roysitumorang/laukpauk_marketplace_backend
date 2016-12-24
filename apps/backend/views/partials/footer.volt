	<!-- Vendor -->
	<script src="/backend/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="/backend/vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="/backend/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="/backend/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
	<script src="/backend/vendor/magnific-popup/jquery.magnific-popup.min.js"></script>
	<script src="/backend/vendor/jquery-placeholder/jquery-placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="/backend/vendor/jquery-ui/jquery-ui.min.js"></script>
	<script src="/backend/vendor/jqueryui-touch-punch/jqueryui-touch-punch.js"></script>
	<script src="/backend/vendor/jquery-appear/jquery-appear.js"></script>
	<script src="/backend/vendor/bootstrap-multiselect/js/bootstrap-multiselect.min.js"></script>
	<script src="/backend/vendor/jquery.easy-pie-chart/jquery.easy-pie-chart.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.min.js"></script>
	<script src="/backend/vendor/flot.tooltip/flot.tooltip.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.pie.min.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.categories.min.js"></script>
	<script src="/backend/vendor/flot/jquery.flot.resize.min.js"></script>
	<script src="/backend/vendor/jquery-sparkline/jquery-sparkline.js"></script>
	<script src="/backend/vendor/raphael/raphael.js"></script>
	<script src="/backend/vendor/morris.js/morris.js"></script>
	<script src="/backend/vendor/gauge/gauge.js"></script>
	<script src="/backend/vendor/snap.svg/snap.svg.js"></script>
	<script src="/backend/vendor/liquid-meter/liquid.meter.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="/backend/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="/backend/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="/backend/javascripts/theme.init.js"></script>

	<!-- Examples -->
	<script src="/backend/javascripts/dashboard/examples.dashboard.js"></script>
	{% if current_user %}
	<script>
		setInterval(() => {
			fetch('/admin/home/inbox', { credentials: 'include' }).then(response => {
				return response.text();
			}).then(payload => {
				document.getElementById('inbox').innerHTML = payload;
				for (let notifications = document.querySelectorAll('.notification'), i = notifications.length; i--; ) {
					notifications[i].onclick = function() {
						fetch('/admin/notifications/update/' + this.dataset.id + '/read:1', {method: 'POST'})
					}
				}
			})
		}, 5000)
	</script>
	{% endif %}
</body>
</html>