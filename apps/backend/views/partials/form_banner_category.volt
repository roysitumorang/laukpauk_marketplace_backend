{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				Nama SLOT<br>
				<input type="text" name="name" value="{{ banner_category.name }}" size="50" class="form form-control form-60">
			</td>
		</tr>
		<tr>
			<td>
				<button type="submitclass="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>