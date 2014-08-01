<?php

function getFooter()
{
  $ret = '';

  $ret .= hykwWPData::get_wp_footer();

  $ret .= <<<EOL
</body>
</html>
EOL;

  return $ret;
}

