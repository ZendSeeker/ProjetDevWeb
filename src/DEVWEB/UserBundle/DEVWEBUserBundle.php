<?php

namespace DEVWEB\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DEVWEBUserBundle extends Bundle
{
  public function getParent()
{
  return 'FOSUserBundle';
}
}
