<section class="body">
	<!-- start: header -->
	{{ partial('partials/top_menu') }}
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		{{ partial('partials/left_side') }}
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<a href="/admin"><h2>Dashboard</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Dashboard</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<div class="panel panel-default">
				<div class="panel-body">
					{{ flashSession.output() }}
					<h2>Welcome {{ current_user.name }}</h2>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 col-lg-12 col-xl-6">
					<section class="panel">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-8">
									<div class="chart-data-selector" id="salesSelectorWrapper">
										<h2>
											Sales:
											<strong>
												<select class="form-control" id="salesSelector">
													<option value="Transaksi Harian" selected>Transaksi Harian</option>
													<option value="Transaksi Bulanan">Transaksi Bulanan</option>
													<option value="Transaksi Tahunan">Transaksi Tahunan</option>
												</select>
											</strong>
										</h2>
										<div id="salesSelectorItems" class="chart-data-selector-items mt-sm">
											<!-- Flot: Sales Porto Admin -->
											<div class="chart chart-sm" data-sales-rel="Transaksi Harian" id="flotDashSales1" class="chart-active"></div>
											<script>
												var flotDashSales1Data = [{
												    data: {{ daily_sales }},
												    color: "#0088cc"
												}];
												// See: assets/javascripts/dashboard/examples.dashboard.js for more settings.
											</script>
											<!-- Flot: Sales Porto Drupal -->
											<div class="chart chart-sm" data-sales-rel="Transaksi Bulanan" id="flotDashSales2" class="chart-hidden"></div>
											<script>
												var flotDashSales2Data = [{
												    data: {{ monthly_sales }},
												    color: "#2baab1"
												}];
												// See: assets/javascripts/dashboard/examples.dashboard.js for more settings.
											</script>
											<!-- Flot: Sales Porto Wordpress -->
											<div class="chart chart-sm" data-sales-rel="Transaksi Tahunan" id="flotDashSales3" class="chart-hidden"></div>
											<script>
												var flotDashSales3Data = [{
												    data: {{ annual_sales }},
												    color: "#734ba9"
												}];
												// See: assets/javascripts/dashboard/examples.dashboard.js for more settings.
											</script>
										</div>
									</div>
								</div>
								<div class="col-lg-4 text-center">
									<h2 class="panel-title mt-md">Sales Goal</h2>
									<div class="liquid-meter-wrapper liquid-meter-sm mt-lg">
										<div class="liquid-meter">
											<meter min="0" max="100" value="35" id="meterSales"></meter>
										</div>
										<div class="liquid-meter-selector" id="meterSalesSel">
											<a href="#" data-val="35" class="active">Monthly Goal</a>
											<a href="#" data-val="28">Annual Goal</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
				<div class="col-md-6 col-lg-12 col-xl-6">
					<div class="row">
						<div class="col-md-12 col-lg-6 col-xl-6">
							<section class="panel panel-featured-left panel-featured-primary">
								<div class="panel-body">
									<div class="widget-summary">
										<div class="widget-summary-col widget-summary-col-icon">
											<div class="summary-icon bg-primary">
												<i class="fa fa-life-ring"></i>
											</div>
										</div>
										<div class="widget-summary-col">
											<div class="summary">
												<h4 class="title">Support Questions</h4>
												<div class="info">
													<strong class="amount">1,281</strong>
													<span class="text-primary">(14 unread)</span>
												</div>
											</div>
											<div class="summary-footer">
												<a class="text-muted text-uppercase">(view all)</a>
											</div>
										</div>
									</div>
								</div>
							</section>
						</div>
						<div class="col-md-12 col-lg-6 col-xl-6">
							<section class="panel panel-featured-left panel-featured-secondary">
								<div class="panel-body">
									<div class="widget-summary">
										<div class="widget-summary-col widget-summary-col-icon">
											<div class="summary-icon bg-secondary">
												<i class="fa fa-usd"></i>
											</div>
										</div>
										<div class="widget-summary-col">
											<div class="summary">
												<h4 class="title">Total Profit</h4>
												<div class="info">
													<strong class="amount">Rp. {{ number_format(total_profit) }}</strong>
												</div>
											</div>
											<div class="summary-footer">
												<a class="text-muted text-uppercase">(withdraw)</a>
											</div>
										</div>
									</div>
								</div>
							</section>
						</div>
						<div class="col-md-12 col-lg-6 col-xl-6">
							<section class="panel panel-featured-left panel-featured-tertiary">
								<div class="panel-body">
									<div class="widget-summary">
										<div class="widget-summary-col widget-summary-col-icon">
											<div class="summary-icon bg-tertiary">
												<i class="fa fa-shopping-cart"></i>
											</div>
										</div>
										<div class="widget-summary-col">
											<div class="summary">
												<h4 class="title">Today's Orders</h4>
												<div class="info">
													<strong class="amount">{{ today_orders }}</strong>
												</div>
											</div>
											<div class="summary-footer">
												<a class="text-muted text-uppercase">(statement)</a>
											</div>
										</div>
									</div>
								</div>
							</section>
						</div>
						<div class="col-md-12 col-lg-6 col-xl-6">
							<section class="panel panel-featured-left panel-featured-quartenary">
								<div class="panel-body">
									<div class="widget-summary">
										<div class="widget-summary-col widget-summary-col-icon">
											<div class="summary-icon bg-quartenary">
												<i class="fa fa-user"></i>
											</div>
										</div>
										<div class="widget-summary-col">
											<div class="summary">
												<h4 class="title">Today's Visitors</h4>
												<div class="info">
													<strong class="amount">19,765</strong>
												</div>
											</div>
											<div class="summary-footer">
												<a class="text-muted text-uppercase">(report)</a>
											</div>
										</div>
									</div>
								</div>
							</section>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<section class="panel">
						<header class="panel-heading">
							<div class="panel-actions">
								<a href="#" class="panel-action panel-action-toggle" data-panel-toggle></a>
								<a href="#" class="panel-action panel-action-dismiss" data-panel-dismiss></a>
							</div>

							<h2 class="panel-title">Produk Terlaris</h2>
							<p class="panel-subtitle">Anda dapat melihat produk terlaris disini.</p>
						</header>
						<div class="panel-body">
							<!-- Flot: Basic -->
							<div class="chart chart-md" id="flotDashBasic"></div>
							<script>
								var flotDashBasicData = {{ best_sales }};
								// See: assets/javascripts/dashboard/examples.dashboard.js for more settings.
							</script>
						</div>
					</section>
				</div>
				<div class="col-md-6">
					<section class="panel">
						<header class="panel-heading">
							<div class="panel-actions">
								<a href="#" class="panel-action panel-action-toggle" data-panel-toggle></a>
								<a href="#" class="panel-action panel-action-dismiss" data-panel-dismiss></a>
							</div>
							<h2 class="panel-title">Penggunaan Server</h2>
							<p class="panel-subtitle">Statistik Penggunaan Server</p>
						</header>
						<div class="panel-body">

							<!-- Flot: Curves -->
							<div class="chart chart-md" id="flotDashRealTime"></div>
						</div>
					</section>
				</div>
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>