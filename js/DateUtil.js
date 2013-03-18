/**
 * Parse date in format "dd-mm-yyyy".
 * 
 * @param input
 *          Type: String. String with date in format "dd-mm-yyyy".
 * @return New Date object or null.
 */
function parseDate(input) {
	var parts = input.match(/(\d+)/g);
	if (parts != null) {
		return new Date(parts[2], parts[1] - 1, parts[0]); // months are 0-based
	} else {
		return null;
	}
}

/**
 * Format Date object as "dd-mm-yyyy" string.
 * 
 * @param date
 *          Type: Date.
 * @return String in format "dd-mm-yyyy" or null.
 */
function formatDate(date) {
	if (date == null) {
		return null;
	}
	var d = date.getDate();
	var m = date.getMonth() + 1;
	var y = date.getFullYear();
	return '' + (d <= 9 ? '0' + d : d) + '-' + (m <= 9 ? '0' + m : m) + '-' + y;
}
