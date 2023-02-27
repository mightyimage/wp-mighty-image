<?php
require_once (ABSPATH . 'wp-admin/includes/plugin-install.php');

function get_plugin_file($plugin_slug) {
  require_once (ABSPATH . '/wp-admin/includes/plugin.php');
  $plugins = get_plugins();

  foreach ($plugins as $plugin_file => $plugin_info) {
    $slug = dirname(plugin_basename($plugin_file));
    if ($slug) {
      if ($slug == $plugin_slug) {
        return $plugin_file;
      }
    }
  }
  return null;
}

function check_file_extension($filename) {
  if (substr(strrchr($filename, '.') , 1) === 'php') {
    return true;
  }
  else {
    return false;
  }
}

function mightyimage_render_mightyimage_setting_page() {
  global $mightyimage_options;

  $mightyimage_options["cname"] = !empty($mightyimage_options["cname"]) ? $mightyimage_options["cname"] : "";
  $mightyimage_options["file_type"] = !empty($mightyimage_options["file_type"]) ? $mightyimage_options["file_type"] : "*.gif;*.png;*.jpg;*.jpeg;*.bmp;*.ico;*.webp";
  $mightyimage_options["custom_files"] = !empty($mightyimage_options["custom_files"]) ? $mightyimage_options["custom_files"] : "favicon.ico\ncustom-directory";
  $mightyimage_options["reject_files"] = !empty($mightyimage_options["reject_files"]) ? $mightyimage_options["reject_files"] : "wp-content/uploads/wpcf7_captcha/*\nwp-content/uploads/imagerotator.swf\ncustom-directory*.mp4";

  if (empty($mightyimage_options["mightyimage_url_endpoint"])) {
    if (empty($mightyimage_options['mightyimage_id']) && empty($mightyimage_options['cname'])) {
      $mightyimage_options["mightyimage_url_endpoint"] = "";
    }
    else if (!empty($mightyimage_options['cname'])) {
      $mightyimage_options["mightyimage_url_endpoint"] = $mightyimage_options['cname'];
    }
    else if (!empty($mightyimage_options['mightyimage_id'])) {
      $mightyimage_options["mightyimage_url_endpoint"] = "https://app.mightyimage.io/" . $mightyimage_options['mightyimage_id'];
    }
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
                           <h2 class="dops-card-title">Steps to configure MightImage.io</h2>
                           <h4>If you haven't created an account with MightImage.io yet, then the first step is to 
            <a href="https://app.mightyimage.io/auth/sign-up" target="_blank">register</a>.
            
        </h4>
                        </div>
                    </div>
                    <form method="post" action="options.php">
                        <?php settings_fields('mightyimage_settings_group'); ?>
                        <div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
                                <label class="mi-form-label"><span class="mi-form-label-wide"><?php _e('MightyImage URL endpoint (or CNAME)', 'mightyimage_domain'); ?></span>
                                  <input id="mightyimage_settings[mightyimage_url_endpoint]" 
										 type="text" 
										 class="dops-text-input" 
                                         name="mightyimage_settings[mightyimage_url_endpoint]" 
                                         value="<?php echo isset($mightyimage_options['mightyimage_url_endpoint']) ? $mightyimage_options['mightyimage_url_endpoint'] : ''; ?>" />
                                </label>
                                <span class="mi-form-setting-explanation">
									Copy paste the MightyImage URL endpoint (or CNAME) from MightyImage <a href="https://app.mightyimage.io/" target="_blank">dashboard</a>. 
									
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php _e('File types', 'mightyimage_domain'); ?></span>
									<input id="mightyimage_settings[file_type]" 
										   type="text"
										   name="mightyimage_settings[file_type]" 
										   value="<?php echo isset($mightyimage_options['file_type']) ? $mightyimage_options['file_type'] : ''; ?>" 
										   class="dops-text-input" />
                                </label>
                                <span class="mi-form-setting-explanation">
									Specify the file types that you want to be loaded via MightyImage
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php _e('Custom files', 'mightyimage_domain');; ?></span>
									<textarea id="mightyimage_settings[custom_files]" 
											  name="mightyimage_settings[custom_files]"
											  class="dops-text-input"
											  cols="40" 
											  rows="5"><?php echo isset($mightyimage_options['custom_files']) ? $mightyimage_options['custom_files'] : ''; ?></textarea>
                                </label>
                                <span class="mi-form-setting-explanation">
									Specify any files or directories outside of theme or other common directories to be loaded via MightyImage
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
                            <fieldset class="mi-form-fieldset">
								<label class="mi-form-label"><span class="mi-form-label-wide"><?php _e('Rejected files', 'mightyimage_domain');; ?></span>
									<textarea id="mightyimage_settings[reject_files]" 
											  name="mightyimage_settings[reject_files]"
											  class="dops-text-input"
											  cols="40" 
											  rows="5"><?php echo isset($mightyimage_options['reject_files']) ? $mightyimage_options['reject_files'] : ''; ?></textarea>
                                </label>
                                <span class="mi-form-setting-explanation">
									Specify any files or directories that you do not want to load via MightyImage
								</span>
                            </fieldset>
						  </div>
						</div>
						
						<div class="mi-form-settings-group">
                          <div class="dops-card mi-form-has-child">
							<fieldset class="mi-form-fieldset">
								<label class="mi-form-label">
									<input type="submit" class="button-primary" value="<?php _e('Save changes', 'mightyimage_domain'); ?>" />
                                </label>
                                <span class="mi-form-setting-explanation">
									Once you save settings, this plugin will load all post images via Mighty Image. If you face any problem, reach out to us at <a href="mailto:support@mightyimage.io" target="_blank">support@mightyimage.io</a> or <a href="https://mightyimage.io/blog" target="_blank">read docs</a>.
								</span>
                            </fieldset>
                          </div>
                      </div>
                    </form>
                </div>
                
            </div>
			<div class="mi-footer">
				<?php $plugin_data = get_plugin_data(MI_PLUGIN_ENTRYPOINT); ?>
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
