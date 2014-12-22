$(document).ready(function() {
if (!FileList) return;

var appid = 'files_clipboard',
	$dir = $('#dir'),
	$fileList = $('#fileList');

var $cut = $('<a/>')
	.attr('id', 'clipboard_cut')
	.append($('<img/>').addClass('svg').attr('src', OC.imagePath(appid,'cut')))
	.append(' ' + t(appid,'Cut'))
	.on('click', cut)
	.hide()
	.appendTo('#headerName .selectedActions');
	
var $copy = $('<a/>')
	.attr('id', 'clipboard_copy')
	.append($('<img/>').addClass('svg').attr('src', OC.imagePath(appid,'copy')))
	.append(' ' + t(appid,'Copy'))
	.on('click', copy)
	.hide()
	.appendTo('#headerName .selectedActions');

var $paste = $('<a/>')
	.attr('id', 'clipboard_paste')
	.append($('<img/>').addClass('svg').attr('alt', t(appid, 'Paste')).attr('src', OC.imagePath(appid,'paste')))
	.addClass('button')
	.on('click', paste)
	.hide()
	.appendTo('#controls .creatable');

var clipboard = JSON.parse(sessionStorage.getItem(appid));
$fileList.on('changeDirectory', function() {
	$fileList.off('DOMNodeRemoved', onRowRemoved);
});
$fileList.on('fileActionsReady', function() {
	$fileList.on('DOMNodeRemoved', onRowRemoved);
});

function onRowRemoved(event) {
	var $target = $(event.target);
	if (clipboard && clipboard.directory == $dir.val() && $target.is('tr[data-file]')) {
		var fileIndex = clipboard.files.indexOf($target.attr('data-file'));
		if (fileIndex != -1) {
			clipboard.files.splice(fileIndex, 1);
			if (!clipboard.files.length) clipboard = null;
		}
		update();
	}
}

function update(blink) {
	var permissions = parseInt($('#permissions').val());

	$cut.toggle((permissions & OC.PERMISSION_READ && permissions & OC.PERMISSION_DELETE) != 0);
	$copy.toggle((permissions & OC.PERMISSION_READ) != 0);

	if (clipboard) {
		var sameDirectory = clipboard.directory == $dir.val(),
			noPermissions = !(permissions & OC.PERMISSION_CREATE),
			disabled = noPermissions || sameDirectory,
			title;
		if (sameDirectory) title = t(appid, 'Unable to paste: the files come from this directory.')
		else if (noPermissions) title = t(appid, 'Unable to paste: you do not have the permissions to create files in this directory.')
		else title = n(appid, 'Paste %n item', 'Paste %n items', clipboard.files.length);
		
		$paste
			.toggleClass('disabled', disabled)
			.attr('title', title)
			.tipsy({gravity:'ne', fade:true})
			.show();
		
		if (clipboard.operation == 'cut' && clipboard.directory == $dir.val()) {
			var $trs = $('tr', $fileList);
			clipboard.files.forEach(function(file) {
				$trs.filterAttr('data-file', file).addClass('cut');
			});
		}
		
		if (blink === true) {
			$paste.addClass('blink');
			setTimeout(function () { $paste.removeClass('blink'); }, 500);
		}
	} else {
		$paste.hide();
	}
};

function clearCut() {
	$('tr[data-file]', $fileList).removeClass('cut');
}

function cut () {
	var files = FileList.getSelectedFiles().map(function(file) { return file.name; });
	clipboard = { operation: 'cut', directory: $dir.val(), files: files };
	sessionStorage.setItem(appid, JSON.stringify(clipboard));
	clearCut();
	clearSelection();
	update(true);
}

function copy () {
	var files = FileList.getSelectedFiles().map(function(file) { return file.name; });
	clipboard = { operation: 'copy', directory: $dir.val(), files: files };
	sessionStorage.setItem(appid, JSON.stringify(clipboard));
	clearCut();
	clearSelection();
	update(true);
}

function clearSelection() {
	$('tr[data-file]', $fileList).removeClass('selected');
	$('tr[data-file] input[type="checkbox"]', $fileList).removeAttr('checked');
}

function paste() {
	if ($(this).hasClass('disabled')) return;
	FileList.showMask();
	clipboard.destination = $dir.val();
	$(window).on('beforeunload', processing);
	replaceExistingFiles(function(replace) {
		if (!replace) FileList.hideMask();
		else $.ajax({
			type: 'POST',
			url: OC.filePath(appid, 'ajax', 'clipboard.php'),
			data: clipboard,
			success: function (data) {
				if (data.status == 'error') {
					var message;
					if (clipboard.operation == 'cut') message = t(appid, "An error occurred during the move.");
					else message = t(appid, "An error occurred during the copy.");
					OC.Notification.hide();
					OC.Notification.show(message);
				} else {
					if (clipboard.operation == 'cut') {
						sessionStorage.removeItem(appid);
						clipboard = null;
					}
				}
				FileList.reload();
			},
			complete: function (request) {
				$(window).off('beforeunload', processing);
			},
			dataType: 'json'
		});
		
	});
}

function processing() {
	if (clipboard.operation == 'cut') message = t(appid, 'Processing. Leaving the page now will interrupt the move.');
	else message = t(appid, 'Processing. Leaving the page now will interrupt the copy.');
}

function replaceExistingFiles(callback) {
	var $trs = $('tr', $fileList);
	var existing = clipboard.files.filter(function(file) {
		return $trs.filterAttr('data-file', file).attr('data-file');
	});
	if (!existing.length) callback(true);
	else {
		var message = t(appid, 'The contents of the clipboard is in conflicts with elements already present in this directory. Do you want to replace them ?');
		OC.dialogs.confirm(message, t(appid, 'Paste'), callback, true);
	}
}

$fileList.on('updated', update);
update();

});
