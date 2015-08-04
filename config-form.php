<?php

defined('MOODLE_INTERNAL') || die();

?>
<fieldset>	
	<div class="form-item clearfix">
		<div class="form-label">
			<label for="allowipaddr"><?php print_string('auth_apilogin_label_allowipaddr', 'auth_apilogin'); ?></label>
		</div>
		<div class="form-setting">
			<div class="form-text">
				<input name="allowipaddr" id="allowipaddr" type="text" value="<?php echo $config->allowipaddr; ?>" style="width:96%" />
				<!--<textarea name="allowipaddr" id="allowipaddr" rows="4"><?php echo $config->allowipaddr; ?></textarea>-->
				<?php echo (isset($err['allowipaddr'])) ?  $OUTPUT->error_text($err['allowipaddr']) : ''; ?>
			</div>
		</div>
		<div class="form-description"><?php print_string('auth_apilogin_info_ipaddrallow', 'auth_apilogin'); ?></div>
	</div>	
	<div class="form-item clearfix">
		<div class="form-label">
			<label for="apikey"><?php print_string('auth_apilogin_label_apikey', 'auth_apilogin'); ?></label>
		</div>
		<div class="form-setting">
			<div class="form-text">
				<input name="apikey" id="apikey" type="text" value="<?php echo $config->apikey; ?>" maxlength="256" style="min-width:280px;" />
				<?php echo (isset($err['apikey'])) ?  $OUTPUT->error_text($err['apikey']) : ''; ?>
			</div>
		</div>
		<div class="form-description"><?php print_string('auth_apilogin_info_apikey', 'auth_apilogin'); ?></div>
	</div>	
	<div class="form-item clearfix">
		<div class="form-label">
			<label for="loginredirect"><?php print_string('auth_apilogin_label_loginredirect', 'auth_apilogin'); ?></label>
		</div>
		<div class="form-setting">
			<div class="form-text">
				<input name="loginredirect" id="loginredirect" type="text" value="<?php echo $config->loginredirect; ?>" maxlength="256" style="width:96%" />
				<?php echo (isset($err['loginredirect'])) ?  $OUTPUT->error_text($err['loginredirect']) : ''; ?>
			</div>
		</div>
		<div class="form-description"><?php print_string('auth_apilogin_info_loginredirect', 'auth_apilogin'); ?></div>
	</div>		
	<div class="form-item clearfix">
		<div class="form-label">
			<label for="passwordurl"><?php print_string('auth_apilogin_label_passwordurl', 'auth_apilogin'); ?></label>
		</div>
		<div class="form-setting">
			<div class="form-text">
				<input name="passwordurl" id="passwordurl" type="text" value="<?php echo $config->passwordurl; ?>" maxlength="256" style="width:96%" />
				<?php echo (isset($err['passwordurl'])) ?  $OUTPUT->error_text($err['passwordurl']) : ''; ?>
			</div>
		</div>
		<div class="form-description"><?php print_string('auth_apilogin_info_passwordurl', 'auth_apilogin'); ?></div>
	</div>		
	<div class="form-item clearfix">
		<div class="form-label">
			<label for="profileurl"><?php print_string('auth_apilogin_label_profileurl', 'auth_apilogin'); ?></label>
		</div>
		<div class="form-setting">
			<div class="form-text">
				<input name="profileurl" id="profileurl" type="text" value="<?php echo $config->profileurl; ?>" maxlength="256" style="width:96%" />
				<?php echo (isset($err['profileurl'])) ?  $OUTPUT->error_text($err['profileurl']) : ''; ?>
			</div>
		</div>
		<div class="form-description"><?php print_string('auth_apilogin_info_profileurl', 'auth_apilogin'); ?></div>
	</div>	
</fieldset>
<table cellspacing="0" cellpadding="5" border="0">
<?php
print_auth_lock_options($this->authtype, $user_fields, get_string('auth_fieldlocks_help', 'auth'), false, false);
?>
</table>
