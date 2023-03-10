<?php
class MightyImageReWriter {
  /**
   * @var string
   */
  private $cdn_url = '';
  private $_regexps = array();
  private $_placeholders = array();
  private $mightyimage_options;

  /**
   * @var string
   */
  private $site_url = '';

  /**
   * Constructor
   *
   * @param string $cdn_url
   * @param string $site_url
   */
  public function __construct($cdn_url, $site_url, $mightyimage_options) {
    // Store cdn url & site url in property
    $this->site_url = $site_url;
    $this->cdn_url = $cdn_url;

    // default values for all options
    $mightyimage_options["file_type"]    = !empty($mightyimage_options["file_type"])     ? $mightyimage_options["file_type"] : "*.gif;*.png;*.jpg;*.jpeg;*.bmp;*.ico;*.webp";
    $mightyimage_options["custom_files"] = !empty($mightyimage_options["custom_files"])  ? $mightyimage_options["custom_files"] : "favicon.ico\n";
    $mightyimage_options["reject_files"] = !empty($mightyimage_options["reject_files"])  ? $mightyimage_options["reject_files"] : "wp-content/uploads/wpcf7_captcha/*\nwp-content/uploads/imagerotator.swf\n";
    $mightyimage_options["webp"]         = (bool)(MightyImageHelper::to_boolean($mightyimage_options["webp"])) ? true : false;
    $mightyimage_options["enabled"]      = (bool)(MightyImageHelper::to_boolean($mightyimage_options["enabled"])) ? true : false;

    $this->mightyimage_options = $mightyimage_options;

    MightyImageHelper::log_debug("Rewriter Options", array(
      "site_url" => $site_url,
      "cdn_url"  => $cdn_url,
      "options"  => $mightyimage_options
    ));
  }

  protected function exclude_asset($path) {
    $reject_files = explode(PHP_EOL, $this->mightyimage_options['reject_files']);

    foreach ($reject_files as $reject_file) {
      if ($reject_file != '') {
        $reject_file = MightyImageHelper::replace_folder_placeholders($reject_file);

        $reject_file = MightyImageHelper::normalize_file($reject_file);

        $reject_file_regexp = '~^(' . MightyImageHelper::get_regexp_by_mask($reject_file) . ')~i';

        if (preg_match($reject_file_regexp, $path)) {
          return true;
        }
      }
    }
    return false;
  }

  protected function get_dir_scope() {
    $input = explode(',', $this->dirs);

    // default
    if ($this->dirs == '' || count($input) < 1) {
      return 'wp\-content|wp\-includes';
    }
    return implode('|', array_map('quotemeta', array_map('trim', $input)));
  }

  //todo next feature is allow custom params to mightyimage to size and optimize image
  protected function extract_img_details($content) {
    preg_match_all('/(.*)-([0-9]+)x([0-9]+)\.([^"\']+)/', $content, $matches);
    $lookup = array(
      'w',
      'h',
      'ct',
      'cl',
      'bf',
      'g',
      'gs',
      'sp',
      'f',
      'q',
      'p'
    );
    $data = array();
    foreach ($matches as $k => $v) {
      foreach ($v as $ind => $val) {
        if (!array_key_exists($ind, $data)) {
          $data[$ind] = array();
        }
        $key = $lookup[$k];
        $data[$ind][$key] = $val;
      }
    }
    return $data;
  }

  protected function rewrite_url($matches) {
    list($match, $quote, $url, , , , $path) = $matches;
    $path = ltrim($path, '/');

    if ($this->exclude_asset($path)) {
      MightyImageHelper::log_debug("Exluded Rewriting", $path);
      return $quote . $url;
    }

    $site_url = $this->site_url;
    $extra = "";

    if($this->mightyimage_options["webp"] == true){
      $extra = "?f=webp";
    }

    $final_url = $this->cdn_url . $path . $extra;

    MightyImageHelper::log_debug("Rewriting", $path . " => " . $final_url);
    return $quote . $final_url;
  }

  public function replace_all_links($buffer) {
    $this->fill_regexps();

    MightyImageHelper::log_debug("MI_REGEXPS", $this->_regexps);

    $srcset_pattern = '~srcset\s*=\s*[\"\'](.*?)[\"\']~';
    $buffer = preg_replace_callback($srcset_pattern, array(
      $this,
      '_srcset_replace_callback'
    ) , $buffer);

    foreach ($this->_regexps as $regexp) {
      $buffer = preg_replace_callback($regexp, array(
        $this,
        'rewrite_url'
      ) , $buffer);
    }

    $buffer = $this->replace_placeholders($buffer);
    return $buffer;
  }

  private function replace_placeholders($buffer) {
    foreach ($this->_placeholders as $srcset_id => $srcset_content) {
      $buffer = str_replace($srcset_id, $srcset_content, $buffer);
    }
    return $buffer;
  }

  function _srcset_replace_callback($matches) {
    list($match, $srcset) = $matches;

    if (empty($this->_regexps)) return $match;
    $index = "%srcset-" . count($this->_placeholders) . "%";

    $srcset_urls = explode(',', $srcset);
    $new_srcset_urls = array();

    foreach ($srcset_urls as $set) {

      preg_match("~(?P<spaces>^\s*)(?P<url>\S+)(?P<rest>.*)~", $set, $parts);
      if (isset($parts['url'])) {

        foreach ($this->_regexps as $regexp) {
          $new_url = preg_replace_callback($regexp, array(
            $this,
            'rewrite_url'
          ) , '"' . $parts['url'] . '">');

          if ('"' . $parts['url'] . '">' != $new_url) {
            $parts['url'] = substr($new_url, 1, -2);
            break;
          }
        }
        $new_srcset_urls[] = $parts['spaces'] . $parts['url'] . $parts['rest'];
      }
      else {
        $new_srcset_urls[] = $set;
      }

    }
    $this->_placeholders[$index] = implode(',', $new_srcset_urls);

    return 'srcset="' . $index . '"';
  }

  private function fill_regexps() {
    $regexps = array();

    $site_path = MightyImageHelper::site_url_uri();
    $domain_url_regexp = MightyImageHelper::home_domain_root_url_regexp();

    $site_domain_url_regexp = false;
    if ($domain_url_regexp != MightyImageHelper::get_url_regexp(MightyImageHelper::url_to_host(site_url()))) $site_domain_url_regexp = MightyImageHelper::get_url_regexp(MightyImageHelper::url_to_host(site_url()));

    // regex for allowed file types
    $mask = $this->mightyimage_options['file_type'];
    if ($mask != '') {
      $regexps[] = '~(["\'(=])\s*((' . $domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($site_path . WPINC) . '/(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';
      if ($site_domain_url_regexp) $regexps[] = '~(["\'(=])\s*((' . $site_domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($site_path . WPINC) . '/(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';

      // allow same file formats for themes
      $theme_dir = preg_replace('~' . $domain_url_regexp . '~i', '', get_theme_root_uri());
      $regexps[] = '~(["\'(=])\s*((' . $domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($theme_dir) . '/(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';

      if ($site_domain_url_regexp) {
        $theme_dir2 = preg_replace('~' . $site_domain_url_regexp . '~i', '', get_theme_root_uri());
        $regexps[] = '~(["\'(=])\s*((' . $site_domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($theme_dir) . '/(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';
        $regexps[] = '~(["\'(=])\s*((' . $site_domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($theme_dir2) . '/(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';
      }

      // allow same file formats for uploads
      $upload_info = MightyImageHelper::upload_info();
      $upload_dir = $upload_info["baseurlpath"];
      $regexps[] = '~(["\'(=])\s*((' . $domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($upload_dir) . '(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';

      if ($site_domain_url_regexp) {
        $regexps[] = '~(["\'(=])\s*((' . $site_domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($upload_dir) . '(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';
        $regexps[] = '~(["\'(=])\s*((' . $site_domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($upload_dir) . '(' . MightyImageHelper::get_regexp_by_mask($mask) . ')([^"\'() >]*)))~i';
      }
    }

    $masks = explode(PHP_EOL, $this->mightyimage_options['custom_files']);

    if (count($masks)) {
      $custom_regexps_urls = array();
      $custom_regexps_uris = array();
      $custom_regexps_docroot_related = array();

      foreach ($masks as $mask) {
        if (!empty($mask)) {
          if (MightyImageHelper::is_url($mask)) {
            $url_match = array();
            if (preg_match('~^((https?:)?//([^/]*))(.*)~', $mask, $url_match)) {
              $custom_regexps_urls[] = array(
                'domain_url' => MightyImageHelper::get_url_regexp($url_match[1]) ,
                'uri' => MightyImageHelper::get_regexp_by_mask($url_match[4])
              );
            }
          }
          elseif (substr($mask, 0, 1) == '/') { // uri
            $custom_regexps_uris[] = MightyImageHelper::get_regexp_by_mask($mask);
          }
          else {
            $file = MightyImageHelper::normalize_path($mask); // \ -> backspaces
            $file = str_replace(MightyImageHelper::site_root() , '', $file);
            $file = ltrim($file, '/');

            $custom_regexps_docroot_related[] = MightyImageHelper::get_regexp_by_mask($mask);
          }
        }
      }

      if (count($custom_regexps_urls) > 0) {
        foreach ($custom_regexps_urls as $regexp) {
          $regexps[] = '~(["\'(=])\s*((' . $regexp['domain_url'] . ')?((' . $regexp['uri'] . ')([^"\'() >]*)))~i';
        }
      }
      if (count($custom_regexps_uris) > 0) {
        $regexps[] = '~(["\'(=])\s*((' . $domain_url_regexp . ')?((' . implode('|', $custom_regexps_uris) . ')([^"\'() >]*)))~i';
      }

      if (count($custom_regexps_docroot_related) > 0) {
        $regexps[] = '~(["\'(=])\s*((' . $domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($site_path) . '(' . implode('|', $custom_regexps_docroot_related) . ')([^"\'() >]*)))~i';
        if ($site_domain_url_regexp) $regexps[] = '~(["\'(=])\s*((' . $site_domain_url_regexp . ')?(' . MightyImageHelper::preg_quote($site_path) . '(' . implode('|', $custom_regexps_docroot_related) . ')([^"\'() >]*)))~i';
      }
    }

    $this->_regexps = $regexps;
  }
}