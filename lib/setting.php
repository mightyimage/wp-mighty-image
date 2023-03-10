<?php

function mightyimage_render_mightyimage_setting_page() {
  global $mightyimage_options;

  $mightyimage_options["file_type"]    = !empty($mightyimage_options["file_type"])    ? $mightyimage_options["file_type"] : "*.gif;*.png;*.jpg;*.jpeg;*.bmp;*.ico;*.webp";
  $mightyimage_options["custom_files"] = !empty($mightyimage_options["custom_files"]) ? $mightyimage_options["custom_files"] : "favicon.ico\ncustom-directory";
  $mightyimage_options["reject_files"] = !empty($mightyimage_options["reject_files"]) ? $mightyimage_options["reject_files"] : "wp-content/uploads/wpcf7_captcha/*\nwp-content/uploads/imagerotator.swf\ncustom-directory*.mp4";
  $mightyimage_options["webp"]         = ((bool) $mightyimage_options["webp"] == true)    ? (bool) $mightyimage_options["webp"] : false;
  $mightyimage_options["enabled"]      = ((bool) $mightyimage_options["enabled"] == true) ? (bool) $mightyimage_options["enabled"] : false;
  
  if(isset($mightyimage_options['mightyimage_id']) && !empty($mightyimage_options["mightyimage_id"]) ){
    $mightyimage_options['mightyimage_id_valid'] = (mightyimage_is_valid_account_id($mightyimage_options['mightyimage_id'])) ? true : false; 
  }
  ob_start();

  wp_enqueue_style('mightyimage', plugins_url('mightyimage') . '/lib/main.css');
?>
<div>
   <div id="mi-plugin-container">
      <div>
         <div>
            <div class="mi-masthead">
               <div class="mi-masthead__inside-container">
                  <div class="mi-masthead__logo-container">
                     <a class="mi-masthead__logo-link" href="#">
                        <img src="<?php echo plugins_url('mightyimage') . '/lib/MightyImage-landscape.svg'?>" class="mightyimage-logo__masthead" height="80">
                     </a>
                  </div>
               </div>
            </div>
            <div class="mi-lower">
                <div class="mi-settings-container">
                    <div>
                        <div class="dops-card mi-settings-description">
                           <h2 class="dops-card-title"><?php esc_html_e('Steps to configure MightyImage.io', 'mightyimage_domain'); ?></h2>
                           <h4><?php esc_html(_e('If you haven\'t created an account with MightyImage.io yet, then the first step is to 
            <a href="https://app.mightyimage.io/auth/sign-up" target="_blank">register</a>.', 'mightyimage_domain')); ?></h4>
                        </div>
                    </div>
                    <form method="post" action="options.php">
                        <?php settings_fields('mightyimage_settings_group'); ?>


            <div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html_e('Enable Mighty Image CDN', 'mightyimage_domain'); ?></span>
									<input id="mightyimage_settings[enabled]" 
										   type="checkbox"
										   name="mightyimage_settings[enabled]" 
                       <?php echo checked( 1, $mightyimage_options['enabled'], false )  ?>
										   value="true" 
								/>
                </label>
							</fieldset>
						  </div>
						</div>           
            <div class="mi-form-settings-group">
              <div class="dops-card mi-form-has-child">
                  <fieldset class="mi-form-fieldset">
                    <label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html_e('Mighty Image Account ID', 'mightyimage_domain'); ?></span>
                                  <input id="mightyimage_settings[mightyimage_id]" 
										 type="text" 
										 class="<?php echo (isset($mightyimage_options['mightyimage_id_valid']) && $mightyimage_options['mightyimage_id_valid'] == true) ? 'valid ' : ''; echo ( isset($mightyimage_options['mightyimage_id_valid']) && $mightyimage_options['mightyimage_id_valid'] == false) ? 'invalid ' : ''; ?> dops-text-input" 
                                         name="mightyimage_settings[mightyimage_id]" 
                                         value="<?php echo isset($mightyimage_options['mightyimage_id']) ? esc_html($mightyimage_options['mightyimage_id']) : ''; ?>" />
                                </label>
                                <span class="mi-form-setting-explanation">
                                <?php  
                                if(isset($mightyimage_options['mightyimage_id_valid'])):
                                  echo '<strong>';
                                  echo ($mightyimage_options['mightyimage_id_valid'] == true) ? esc_html_e('Your account is valid and recognized ', 'mightyimage_domain') : esc_html_e('Your account is not valid or recognized by Mighty Image', 'mightyimage_domain'); 
                                  echo '</strong><br /><br />';
                                endif;
                                ?>

                                <?php esc_html(_e('Copy paste the Mighty Image account ID from Mighty Image <a href="https://app.mightyimage.io/" target="_blank">dashboard</a>. ', 'mightyimage_domain')) ?>
								</span>
							</fieldset>
						  </div>
						</div>
            <div class="mi-form-settings-group">
              <div class="dops-card mi-form-has-child">
                  <fieldset class="mi-form-fieldset">
                    <label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html(_e('Or Mighty Image URL endpoint (or CNAME)', 'mightyimage_domain')); ?></span>
                                  <input id="mightyimage_settings[mightyimage_url_endpoint]" 
										 type="text" 
										 class="dops-text-input" 
                                         name="mightyimage_settings[mightyimage_url_endpoint]" 
                                         value="<?php echo isset($mightyimage_options['mightyimage_url_endpoint']) ? esc_html($mightyimage_options['mightyimage_url_endpoint']) : ''; ?>" />
                                </label>
                                <span class="mi-form-setting-explanation">
                                <?php esc_html(_e('Copy paste the Mighty Image URL endpoint (or CNAME) from Mighty Image <a href="https://app.mightyimage.io/" target="_blank">dashboard</a>. ', 'mightyimage_domain')) ?>
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html_e('File types', 'mightyimage_domain'); ?></span>
									<input id="mightyimage_settings[file_type]" 
										   type="text"
										   name="mightyimage_settings[file_type]" 
										   value="<?php echo isset($mightyimage_options['file_type']) ? esc_html($mightyimage_options['file_type']) : ''; ?>" 
										   class="dops-text-input" />
                </label>
                <span class="mi-form-setting-explanation">
                <?php esc_html(_e('Specify the file types that you want to be loaded via Mighty Image', 'mightyimage_domain')) ?>
								</span>
							</fieldset>
						  </div>
						</div>
            <div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html_e('Enable Webp Extensie', 'mightyimage_domain'); ?></span>
									<input id="mightyimage_settings[webp]" 
										   type="checkbox"
										   name="mightyimage_settings[webp]" 
                       <?php echo checked( 1, $mightyimage_options['webp'], false )  ?>
										   value="true" 
								/>
                </label>
                <span class="mi-form-setting-explanation"><?php esc_html_e('Enable webp functionality via Mighty Image', 'mightyimage_domain') ?></span>
							</fieldset>
						  </div>
						</div>
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html_e('Custom files', 'mightyimage_domain'); ?></span>
									<textarea id="mightyimage_settings[custom_files]" 
											  name="mightyimage_settings[custom_files]"
											  class="dops-text-input"
											  cols="40" 
											  rows="5"><?php echo isset($mightyimage_options['custom_files']) ? esc_html($mightyimage_options['custom_files']) : ''; ?></textarea>
                                </label>
                                <span class="mi-form-setting-explanation">
                                <?php esc_html_e('Specify any files or directories outside of theme or other common directories to be loaded via Mighty Image', 'mightyimage_domain') ?>
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php esc_html_e('Rejected files', 'mightyimage_domain'); ?></span>
									<textarea id="mightyimage_settings[reject_files]" 
											  name="mightyimage_settings[reject_files]"
											  class="dops-text-input"
											  cols="40" 
											  rows="5"><?php echo isset($mightyimage_options['reject_files']) ? esc_html($mightyimage_options['reject_files']) : ''; ?></textarea>
                                </label>
                                <span class="mi-form-setting-explanation">
                                <?php esc_html_e('Specify any files or directories that you do not want to load via Mighty Image', 'mightyimage_domain') ?>
								</span>
                            </fieldset>
						  </div>
						</div>
						
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
							<fieldset class="mi-form-fieldset">
								<label class="mi-form-label">
									<input type="submit" class="button-primary" value="<?php esc_html_e('Save changes', 'mightyimage_domain'); ?>" />
                                </label>
                                <span class="mi-form-setting-explanation">
                                <?php esc_html(_e('Once you save settings, this plugin will load all post images via Mighty Image. If you face any problem, reach out to us at <a href="mailto:support@mightyimage.io" target="_blank">support@mightyimage.io</a> or <a href="https://mightyimage.io/blog" target="_blank">read docs</a>.', 'mightyimage_domain')) ?>
								</span>
                            </fieldset>
                          </div>
                      </div>
                    </form>
                </div>
                
            </div>
			<div class="mi-footer">
				<?php $plugin_data = get_plugin_data(MightyImage_PluginEntrypoint); ?>
			    <ul class="mi-footer__links">
				    <li class="mi-footer__link-item"><a href="https://mightyimage.io/" target="_blank" rel="noopener noreferrer" class="mi-footer__link"><?php echo $plugin_data['Name'] ?> version <?php echo $plugin_data['Version'] ?></a></li>
				</ul>
			</div>
         </div>
      </div>
   </div>
</div>
<?php
  echo ob_get_clean();
}

/**
 * Validate Mighty Image Account ID.
 *
 * @param string $account   RAW code to check.
 *
 * @return bool  true if valid, false otherwise.
 */
function mightyimage_is_valid_account_id( string $account ):bool {
  // 1: empty.
  if ( empty( $account ) ) {
      return false;
  }
  // 2: check length
  if ( 20 < strlen( trim( $account ) ) ) {
      return false;
  }
  // 3: validation account id 
  $url = MightyImage_Validation_Url.'/exists?id='.$account;
  $request = wp_remote_get( $url, array('user-agent' => "mighty-image-wp-plugin") );
  if( is_wp_error( $request ) ) {
    return false; // Bail early
  }
  $body = wp_remote_retrieve_body( $request );
  $data = json_decode( $body );
  if($data->result == false){
    return false;
  }
  // Passed successfully.
  return true;
}

function mightyimage_add_setting_link() {
  add_options_page('MightyImage settings', 'MightyImage settings', 'manage_options', 'mightyimage-setting', 'mightyimage_render_mightyimage_setting_page');
}
add_action('admin_menu', 'mightyimage_add_setting_link');

function mightyimage_register_settings() {
  add_filter('admin_body_class', function ($classes) {
    $classes .= ' ' . 'mightyimage-pagestyles ';
    return $classes;
  });
  register_setting('mightyimage_settings_group', 'mightyimage_settings');
}

add_action('admin_init', 'mightyimage_register_settings');
?>