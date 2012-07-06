<?php

  //require '../helpers/shipworks_functions.php'

  class ShipWorks_Module extends Core_ModuleBase
  {
    protected function createModuleInfo()
    {
      return new Core_ModuleInfo(
      "ShipWorks Lemonstand Bridge",
      "Interfaces with a ShipWorks installation",
      "ZipLineGear.com" );
    }
  }

?>