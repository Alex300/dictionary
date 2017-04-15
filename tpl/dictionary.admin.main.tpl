<!-- BEGIN: MAIN -->
{FILE "{PHP.cfg.themes_dir}/admin/{PHP.cfg.admintheme}/warnings.tpl"}

<!-- IF {TOTAL_ITEMS} > 0 -->
<table class="table table-striped">
	<thead>
		<tr>
			<th>#</th>
			<th>{PHP.L.Title}</th>
			<th></th>
			<th></th>
			<th class="text-center">{PHP.L.dict_value_cnt}</th>
			<th></th>
			<th class="width5">ID#</th>
		</tr>
	</thead>
	<tbody>
		<!-- BEGIN: DICTIONARY_ROW -->
		<tr id="dict-row-{DICT_ROW_ID}" class="dict-row">
			<th scope="row">{DICT_ROW_NUM}</th>
			<td><a href="{DICT_ROW_URL}">{DICT_ROW_TITLE}</a></td>
			<td>{DICT_ROW_PARENT_TITLE}</td>
			<td>{DICT_ROW_PARENT2_TITLE}</td>
			<td class="text-center">{DICT_ROW_VALUE_COUNT}</td>
			<td>
				<a href="{DICT_ROW_URL}" class="btn btn-sm btn-default"
						title="{PHP.L.Open}" data-toggle="tooltip"><span class="fa fa-folder-open-o"></span></a>
				<button  class="dict-edit btn btn-sm btn-info"
				   title="{PHP.L.Edit}" data-toggle="tooltip"><span class="fa fa-edit"></span></button>
				<a href="{DICT_ROW_DELETE_URL}" class="confirmLink btn btn-sm btn-danger"
				   title="{PHP.L.Delete}" data-toggle="tooltip"><span class="fa fa-trash"></span></a>
			</td>
			<td>{DICT_ROW_ID}</td>
		</tr>
		<!-- END: DICTIONARY_ROW -->
	</tbody>
</table>
<!-- ENDIF -->

<!-- BEGIN: ADDFORM -->
<button id="add-dict" class="btn btn-primary add-dict"><span class="fa fa-plus"></span> Добавить словарь</button>

<div class="modal fade" id="editDictDialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Добавить новый словарь</h4>
			</div>
			<form action="{NEW_FORM_URL}" method="post">
				<input type="hidden" name="act" value="save" />
				<input type="hidden" id="editDictDialog-did" name="did" value="0" />
				<div class="modal-body">
					<div class="form-group">
						<label>{PHP.L.Title}</label>
						{NEW_TITLE}
					</div>

					<div class="form-group">
						<label>Родительский словарь</label>
						{NEW_PARENT}
					</div>

					<div class="form-group">
						<label>Родительский словарь 2</label>
						{NEW_PARENT2}
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><span class="fa fa-floppy-o"></span> {PHP.L.Save}</button>
					<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-times-circle-o"></span> {PHP.L.Cancel}</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	'use strict';

	<!-- IF {IS_ERROR} == 1 -->
	$(function() {
		// Handler for .ready() called.
		$('#editDictDialog').modal();
	});
	<!-- ENDIF -->

	$( document ).on( "click", "button.add-dict", function(e) {

		var dialog = $('#editDictDialog');

		// очищаем шаблон от предустановленых значений
		var t = dialog.find('input[type="text"]');
		t.val('');
		t.html('');
		t = dialog.find('select');
		t.val('');
		$('#editDictDialog-did').val('0');
		dialog.modal();
	});

	$( document ).on( "click", ".dict-edit", function(e) {
		// id словаря
		var did = $(this).parents('.dict-row').attr('id');
		did = did.replace('dict-row-', '');
		if(did < 1) return;

		var x = $('input[name=x]').val();

		$('#editDictDialog-did').val(did);

		var parent = $(this);

		var lLeft = parent.width() / 2 - 110;
		var lTop = parent.height() / 2 + 9;
		if ((lTop + 19) > parent.height()) lTop = 2;
		var bgspan = $('<span>', {
			id: "loading",
			class: "loading"
		})  .css('position', 'absolute')
				.css('left',lLeft + 'px')
				.css('top', lTop  + 'px');
		bgspan.html('<img src="images/spinner.gif" alt="loading"/>');
		parent.append(bgspan).css('position', 'relative');

		// Получить данные правила и заполнить форму
		$.post( "{PHP|cot_url('admin', 'm=other&p=dictionary&a=ajxDictionaryInfo', '', 1)}", {did: did, x: x}, function( data ) {
			if(data.error == ''){
				// Заполнить форму
				$('#editDictDialog-title').val(data.dict.title);
				$('#editDictDialog-parent').val(data.dict.parent);
				$('#editDictDialog-parent2').val(data.dict.parent2);

				bgspan.remove();
				// Показать диалог
				$('#editDictDialog').modal();

			}else{
				bgspan.remove();
				alert( data.error );
			}
		}, "json").fail(function() {
			bgspan.remove();
			alert( "Во время получения данных возникла ошибка" );
		});

	});
</script>
<!-- END: ADDFORM -->
<!-- END: MAIN -->