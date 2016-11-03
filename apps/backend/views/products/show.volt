{include file='header.html'}

	<body>
		<section class="body">

			<!-- start: header -->
			{include file='top_menu.html'}
			<!-- end: header -->

			<div class="inner-wrapper">
				<!-- start: sidebar -->
				{include file='left_side.html'}
				<!-- end: sidebar -->

				<section role="main" class="content-body">
					<header class="page-header">
						<a href="products.php?do=detail&id={$detailItem.id}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}"><h2>Detail Produk</h2></a>
					
						<div class="right-wrapper pull-right">
							<ol class="breadcrumbs">
								<li>
									<a href="main.php">
										<i class="fa fa-home"></i>
									</a>
								</li>
								<li><span><a href="products.php?vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}">Produk List</a></span></li>
								<li><span><a href="products.php?do=detail&id={$detailItem.id}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}">Detail Produk</a></span></li>
								<li><span><a href="products.php?do=type&idproduct={$detailItem.id}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}">Produk Type</a></span></li>
							</ol>
					
							<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
						</div>
					</header>
					
<!-- start: page -->
	<header class="panel-heading">
		<h2 class="panel-title">#{$detailItem.vProductID} - {$detailItem.vName}</h2>
		<span style="float:right; margin-top:-23px;"><a href="products.php?do=add&show=edit&id={$detailItem.id}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}" title="ubah data produk ini"><i class="fa fa-pencil-square fa-2x"></i></a></span>
	</header>
<div class="panel-body">
<!-- Content //-->

{$reportMessage}

<table class="table table-striped">
<tr>
	<td bgcolor="#ccf2ff">
	<font size="3"><b>Category:</b>&nbsp;{$detailCategory.vCategoryName}</font>
    {*
	{if $detailCategory.vPictureName != ''}
	<br /><img src="{$dirProducts}{$detailCategory.vPictureName}" border="0" />
	{/if}
    *}	
	</td>
	<td bgcolor="#fff2e5">
	<font size="3"><b>Harga:</b>&nbsp;{if $detailItem.iPrice == '0'}<i>Tidak terdapat harga</i>{else}Rp. {$detailItem.iPrice|number_format}{/if}</font>
	</td>
	<td bgcolor="#e5e5ff">
	<font size="3"><b>Berat:</b>&nbsp;{$detailItem.iWeight|number_format} gram</font>
	</td>
</tr>
<tr>
	<td colspan="2" rowspan="5" width="10%">
	{if $detailItem.vGambar1 == ''}<img src="assets/images/no_picture_550.png" border="0" />{else}
	<a class="image-popup-no-margins" href="{$dirProducts}{$detailItem.vGambar1}">
	<img src="{$relativePath}thumb.php?src={$relativePath}{$dirProducts}{$detailItem.vGambar1}&w=600&h=450&zc=1" border="0" />
	</a>
	<br />
	<a href="javascript:validasi('Anda yakin akan menghapus gambar ini ?','products.php?do=detail&vPile={$detailItem.vGambar1}&id={$detailItem.id}&vGambar=vGambar1&submit=yes&action=deleteitemicon&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}');"><i class="fa fa-trash-o fa-2x"></i></a>
	{/if}
	</td>
</tr>
<tr>
	<td>
	{if $detailItem.vGambar2 == ''}<img src="assets/images/no_picture_150.png" border="0" />{else}
	<a class="image-popup-no-margins" href="{$dirProducts}{$detailItem.vGambar2}"><img src="{$relativePath}thumb.php?src={$relativePath}{$dirProducts}{$detailItem.vGambar2}&w=150&h=100&zc=1" border="0" /></a>&nbsp;
	<a href="javascript:validasi('Anda yakin akan menghapus gambar ini ?','products.php?do=detail&vPile={$detailItem.vGambar2}&id={$detailItem.id}&vGambar=vGambar2&submit=yes&action=deleteitemicon&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}');"><i class="fa fa-trash-o"></i></a>
	{/if}
	</td>
</tr>
<tr>
	<td>
	{if $detailItem.vGambar3 == ''}<img src="assets/images/no_picture_150.png" border="0" />{else}
	<a class="image-popup-no-margins" href="{$dirProducts}{$detailItem.vGambar3}"><img src="{$relativePath}thumb.php?src={$relativePath}{$dirProducts}{$detailItem.vGambar3}&w=150&h=100&zc=1" border="0" /></a>&nbsp;
	<a href="javascript:validasi('Anda yakin akan menghapus gambar ini ?','products.php?do=detail&vPile={$detailItem.vGambar3}&id={$detailItem.id}&vGambar=vGambar3&submit=yes&action=deleteitemicon&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}');"><i class="fa fa-trash-o"></i></a>
	{/if}
	</td>
</tr>
<tr>
	<td>
	{if $detailItem.vGambar4 == ''}<img src="assets/images/no_picture_150.png" border="0" />{else}
	<a class="image-popup-no-margins" href="{$dirProducts}{$detailItem.vGambar4}"><img src="{$relativePath}thumb.php?src={$relativePath}{$dirProducts}{$detailItem.vGambar4}&w=150&h=100&zc=1" border="0" /></a>&nbsp;
	<a href="javascript:validasi('Anda yakin akan menghapus gambar ini ?','products.php?do=detail&vPile={$detailItem.vGambar4}&id={$detailItem.id}&vGambar=vGambar4&submit=yes&action=deleteitemicon&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}');"><i class="fa fa-trash-o"></i></a>
	{/if}
	</td>
</tr>
<tr>
	<td>
	{if $detailItem.vGambar5 == ''}<img src="assets/images/no_picture_150.png" border="0" />{else}
	<a class="image-popup-no-margins" href="{$dirProducts}{$detailItem.vGambar5}"><img src="{$relativePath}thumb.php?src={$relativePath}{$dirProducts}{$detailItem.vGambar5}&w=150&h=100&zc=1" border="0" /></a>&nbsp;
	<a href="javascript:validasi('Anda yakin akan menghapus gambar ini ?','products.php?do=detail&vPile={$detailItem.vGambar5}&id={$detailItem.id}&vGambar=vGambar5&submit=yes&action=deleteitemicon&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}');"><i class="fa fa-trash-o"></i></a>
	{/if}	
	</td>
</tr>
</table>

<table class="table table-striped">
{if $listType[0].Item.id != ''}
<tr>
	<td colspan="3">
	<strong>Type Produk:</strong>&nbsp;
	<a href="products.php?do=type&idproduct={$detailItem.id}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}" title="Update Tipe Produk"><i class="fa fa-pencil-square"></i></a>
	<ul>
    {section name=i loop=$listType step=1}
    	<li>{$listType[i].Item.vGroup} {$listType[i].Item.vType} (Stock: {$listType[i].Item.iStock|number_format}){if $listType[i].Item.iPriceAdd != '' AND $listType[i].Item.iPriceAdd != '0'} + Rp. {$listType[i].Item.iPriceAdd|number_format}{/if}</li>
    {/section}
	</ul>
    </td>
</tr>
{/if}
{if $listDimensi[0].Item.id != ''}
<tr>
	<td colspan="3">
	<strong>Dimensi Produk:</strong>&nbsp;
	<a href="products.php?do=dimensi&idproduct={$detailItem.id}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}" title="Update Dimensi Produk"><i class="fa fa-pencil-square"></i></a>
	<ul>
    {section name=i loop=$listDimensi step=1}
    	<li>{$listDimensi[i].Item.vDimension}: {$listDimensi[i].Item.iDimension} {$listDimensi[i].Item.vSatuan}</li>
    {/section}
	</ul>
    </td>
</tr>
{/if}
<tr>
	<td>
	<b>Permalink:</b>&nbsp;{$detailItem.vPermalink|no_value}
	</td>
	<td colspan="2">
	<b>Poin Beli:</b>&nbsp;{$detailItem.iPoinBuy|number_format}<br />
	<b>Poin Affiliasi:</b>&nbsp;{$detailItem.iPoinAff|number_format}
	</td>
	{*
	<td>
	<b>Discount:</b>&nbsp;{if $detailItem.iPercent|number_format == '0'}<s>{$detailItem.iPercent|number_format}%</s><br /><i>No Discount</i>{else}<font size="3"><strong>{$detailItem.iPercent|number_format}%</strong></font>{/if}
	</td>
	*}
</tr>
<tr>
	<td>
	<b>Upload:</b>&nbsp;{$detailItem.dPublishDate|date_format:"%A, %e %B %Y"}
	</td>
	<td>
	<b>Tampilkan ?:</b>&nbsp;{if $detailItem.iShow == '1'}YA{else}<font color="#FF0000">TIDAK</font>{/if}
	</td>
	<td>
	<b>Status:</b>&nbsp;{if $detailItem.iStatus == '0'}<i class="fa fa-check-square"></i>&nbsp;Tersedia{else}<font color="#FF0000"><i class="fa fa-phone-square"></i>&nbsp;<i>Call Only</i></font>{/if} / 
    <strong>Stock:</strong>&nbsp;{$Stock|number_format}
	</td>
</tr>
<tr>
	<td colspan="3">
	{$detailItem.tDetail}
	</td>
</tr>
</table>

{if $tLokasi != ''}
<table class="table table-striped">
<thead>
<tr><th><b>Berlaku untuk daerah</b></th></tr>
</thead>
<tbody>
<tr>
	<td>
	<ul>
	{section name=i loop=$tLokasi step=1}
	<li>{$tLokasi[i].Item.vCity|no_value}</li>
	{/section}
	</ul>
	</td>
</tbody>
</table>
{/if}

<!-- eof Content //-->
</div>
<!-- end: page -->
				
				</section>
			</div>

			{include file='right_side.html'}
			
		</section>

{include file='footer.html'}