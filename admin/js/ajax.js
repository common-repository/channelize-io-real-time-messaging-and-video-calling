jQuery(document).ready(function ($) {
	/** Ajax Request For Create User Migration Tar File On Temp Location **/
	jQuery("#create-tar-migration").on("click", function (e) {
		e.preventDefault();
		var user_checked_status = jQuery("#Users-migration-status").is(
			":checked"
		);
		if (user_checked_status == false) {
			var user_status = 0;
			alert("Please click over Users CheckBox");
			return;
		}
		jQuery.ajax({
			url: channelize_ajax_url.ajax_url,
			type: "post",
			data: {
				action: "create_tar_from_users_data",
				user_checked_status: user_checked_status,
			},
			success: function (response) {
				var obj = JSON.parse(response);
				if (obj.status == "success") {
					alert("Tar Created successFully Done");
					jQuery("#channelize-download-tar-sub").css(
						"display",
						"block"
					);
					jQuery(
						'#nav-tab a[href="#channelize-download-tar"]'
					).trigger("click");
				} else {
					alert(response);
				}
			},
		});
	});

	/**  switch to enable chat after click on download tar **/
	var toggleMyForm = jQuery("#toggle-my-form");
	toggleMyForm.on("click", function () {
		alert("Now Activate Your channelize");
		jQuery('#nav-tab a[href="#activate-channelize-migration"]').trigger(
			"click"
		);
		jQuery("#activate-channelize-migration").css("display", "block");
	});

	/** trigger tab according to the cookie**/
	var cookieVal = getCookieName("channelize_migration_step_status");
	if (!cookieVal == "" || !cookieVal == null) {
		if (cookieVal == "") {
			return false;
		} else if (cookieVal == 1) {
			jQuery("#channelize-download-tar-sub").css("display", "block");
			jQuery('#nav-tab a[href="#channelize-download-tar"]').trigger(
				"click"
			);
		} else {
			jQuery("#channelize-download-tar-sub").css("display", "block");
			jQuery("#activate-channelize-migration").css("display", "block");
			jQuery('#nav-tab a[href="#activate-channelize-migration"]').trigger(
				"click"
			);
		}
	}

	/** fill the user roles settings **/
	var optionsToSelect = jQuery(".multi-select-userrole").val();
	if (!optionsToSelect == "" || !optionsToSelect == null) {
		var select = document.getElementById("channelize_userrole_set");
		for (var i = 0, l = select.options.length, o; i < l; i++) {
			o = select.options[i];
			if (optionsToSelect.indexOf(o.text) != -1) {
				o.selected = true;
			}
		}
	}

	/** get the cookie value from browser **/
	function getCookieName(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(";");
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == " ") {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}

});