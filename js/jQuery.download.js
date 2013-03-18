/**
 * jQuery plugin for Ajax-like file downloads. Assuming the server has no problems handling the request, the front end
 * will respond with a prompt for a file download opened in new window or tab (due to target=_blank) and the user never
 * needs to leave the page.
 * 
 * Examples of usage:
 * 
 * <code>
 * $.download('links/grade_reports.model.php?func=getPdfReport', 'grade-report-student-unique-id=' +
 * 	grade_report_student_unique_id + '&grade-report-unique-id=' + grade_report_unique_id, 'post');
 * $.download('links/grade_reports.model.php?func=previewPdfReport', $('#grade-reports-form').serialize(), 'post');
 * </code>
 * 
 * The plugin accepts 3 arguments:
 * 
 * @param url
 *          Type: String. A string containing the URL to which the request is sent.
 * @param data
 *          Type: String. A string that is sent to the server with the request. Format of the string:
 *          key1=value1&key2=value2&...
 * @param method
 *          Type: String. A string containing HTTP method: "get" or "post" (default).
 */
jQuery.download = function(url, data, method) {
	// url and data options required
	if (url && data) {
		// data can be string of parameters or array/object
		data = typeof data == 'string' ? data : jQuery.param(data);
		// split params into form inputs
		var inputs = '';
		jQuery.each(data.split('&'), function() {
			var pair = this.split('=');
			inputs += '<input type="hidden" name="' + pair[0] + '" value="' + pair[1] + '" />';
		});
		// send request
		jQuery('<form action="' + url + '" method="' + (method || 'post') + '" target=_blank>' + inputs + '</form>').appendTo('body').submit().remove();
	}
};
