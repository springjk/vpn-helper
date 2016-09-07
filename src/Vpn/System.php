<?php
namespace Springjk\Vpn;

use Springjk\System\Mac;

class System
{
    static public function create()
    {
//        $os_type = $this->getOSType();
//
//        switch ($os_type) {
//            case 'linux':
//                $system = new Linux();
//                break;
//            case 'macOS':
//                $system = new Mac();
//                break;
//            case 'windows':
//                $system = new Windows();
//                break;
//            default:
//                throw new \Exception('not support os type');
//                break;
//        }

        $system = new Mac();

        return $system;
    }


    /**
     * get php server OS type
     *
     * @return string OS type
     */
    public function getOSType()
    {
        switch (PHP_OS) {
            case 'Linux':
                $os_type = 'linux';
                break;
            case 'Darwin':
                $os_type = 'macOS';
                break;
            case 'WIN32':
            case 'WINNT':
            case 'Windows':
                $os_type = 'windows';
                break;
            default:
                $os_type = 'others';
                break;
        }

        return $os_type;
    }
}
