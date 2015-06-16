<!-- BEGIN: MAIN -->
{FILE "{PHP.cfg.themes_dir}/admin/{PHP.cfg.admintheme}/warnings.tpl"}

<div class="row">
	<div class="col-xs-12 col-md-7">
		<div class="panel panel-inverse margintop20">
			<div class="panel-heading">
				<h4 class="panel-title">{PAGE_TITLE}</h4>
			</div>
			<div class="panel-body">
				<form method="post" action="{MASS_SAVE_URL}">
					{MASS_SAVE_HIDDEN}
					<table class="table table-striped">
						<thead>
							<tr>
								<th class="width5">#</th>
								<th>{PHP.L.Title}</th>
								<!-- IF {PARENT_TITLE} --><th class="width25">{PARENT_TITLE}</th><!-- ENDIF -->
								<!-- IF {PARENT2_TITLE} --><th class="width25">{PARENT2_TITLE}</th><!-- ENDIF -->
								<th class="width5"></th>
								<th class="width5">id#</th>
							</tr>
						</thead>
						<tbody>
							<!-- BEGIN: LIST_ROW -->
							<tr id="list-row-{LIST_ROW_ID}" class="">
								<th scope="row">{LIST_ROW_NUM}</th>
								<td>{LIST_ROW_EDIT_VALUE}</td>
								<!-- IF {LIST_ROW_EDIT_PARENT} --><td>{LIST_ROW_EDIT_PARENT}</td><!-- ENDIF -->
								<!-- IF {LIST_ROW_EDIT_PARENT2} --><td>{LIST_ROW_EDIT_PARENT2}</td><!-- ENDIF -->
								<td>
									<a href="{LIST_ROW_DELETE_URL}" class="confirmLink btn btn-sm btn-danger"
									   title="{PHP.L.Delete}" data-toggle="tooltip"><span class="fa fa-trash"></span></a>
								</td>
								<td>{LIST_ROW_ID}</td>
							</tr>
							<!-- END: LIST_ROW -->

							<!-- BEGIN: EMPTY -->
							<tr>
								<td colspan="6">
									<h4 class="text-center help-block">{PHP.L.dict_no_records}</h4>
								</td>
							<tr>
							<!-- END: EMPTY -->
						</tbody>
					</table>

					<button type="submit" class="btn btn-primary"><span class="fa fa-floppy-o"></span> {PHP.L.Save}</button>
				</form>

				<!-- IF {PAGINATION} -->
				<div class="text-right">
					<nav>
						<ul class="pagination" style="margin-bottom: 0">
							{PAGEPREV}{PAGINATION}{PAGENEXT}
						</ul>
					</nav>
					<span class="help-block">{PHP.L.Total}: {TOTALITEMS}, {PHP.L.Onpage}: {ON_THIS_PAGE}</span>
				</div>
				<!-- ENDIF -->
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-md-5">
		<!-- BEGIN: FILTER -->
		<div class="panel panel-inverse margintop20">
			<div class="panel-heading">
				<h4 class="panel-title"><span class="fa fa-filter"></span>  {PHP.L.Filters}</h4>
			</div>
			<div class="panel-body">
				<form action="{FILTER_FORM_URL}" method="get" class="form-horizontal">
					{FILTER_HIDDEN}
					<div class="form-group">
						<label class="col-sm-3 control-label">{PHP.L.Title}</label>
						<div class="col-sm-9">{FILTER_VALUE}</div>
					</div>
					<!-- IF {NEW_PARENT} -->
					<div class="form-group">
						<label class="col-sm-3 control-label">{PARENT_TITLE}</label>
						<div class="col-sm-9">{FILTER_PARENT}</div>
					</div>
					<!-- ENDIF -->
					<!-- IF {NEW_PARENT2} -->
					<div class="form-group">
						<label class="col-sm-3 control-label">{PARENT2_TITLE}</label>
						<div class="col-sm-9">{FILTER_PARENT2}</div>
					</div>
					<!-- ENDIF -->
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-9">
							<button type="submit" class="btn btn-primary"><span class="fa fa-search"></span> {PHP.L.Show}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!-- END: FILTER -->

		<!-- BEGIN: ADDFORM -->
		<div class="panel panel-primary margintop20">
			<div class="panel-heading">
				<h4 class="panel-title"><span class="fa fa-plus"></span>  {PHP.L.dict_add_value}</h4>
			</div>
			<div class="panel-body">
				<form action="{NEW_FORM_URL}" method="post" class="form-horizontal">
					{NEW_HIDDEN}
					<div class="form-group {PHP|cot_formGroupClass('value')}">
						<label class="col-sm-3 control-label">{PHP.L.Title}</label>
						<div class="col-sm-9">{NEW_VALUE}</div>
					</div>
					<!-- IF {NEW_PARENT} -->
					<div class="form-group">
						<label class="col-sm-3 control-label">{PARENT_TITLE}</label>
						<div class="col-sm-9">{NEW_PARENT}</div>
					</div>
					<!-- ENDIF -->
					<!-- IF {NEW_PARENT2} -->
					<div class="form-group">
						<label class="col-sm-3 control-label">{PARENT2_TITLE}</label>
						<div class="col-sm-9">{NEW_PARENT2}</div>
					</div>
					<!-- ENDIF -->
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-9">
							<button type="submit" class="btn btn-primary"><span class="fa fa-floppy-o"></span> {PHP.L.Save}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!-- END: ADDFORM -->
	</div>

</div>
<!-- END: MAIN -->