<?php

namespace o;

class OModule implements \JsonSerializable {

    private $namespace;
    private $baseName;

    function __construct ($namespace, $path) {
        $this->namespace = $namespace;
        $base = basename($path, '.' . Tht::getExt());
        $this->baseName = $namespace . "\\" . u_($base);
    }

    function __call ($f, $args) {
        $qf = $this->namespace . "\\" . $f;
        if (!function_exists($qf)) {
            Tht::error("Unknown function: `$f`");
        }
        return call_user_func_array($qf, $args);
    }

    function newObject($className, $args) {
        $qc = $this->namespace . "\\" . u_($className);

        $o = new $qc ();
        $o->_init($args);

        return $o;
    }

    function __toString() {
        return '<<<' . Tht::cleanPackageName($this->baseName) . '>>>';
    }

    function jsonSerialize() {
        return $this->__toString();
    }
}

// Adapter for sideloaded module calls
class OModulePhpAdapter extends OModule {

    private $mod;

    function __construct ($mod) {
        $this->mod = $mod;
    }

    function __call ($f, $args) {
        $fnCall = function() use ($f, $args) {
            $uf = u_($f);
            $ret = $this->mod->__call($uf, $args);
            return uv($ret);
        };
        return ErrorHandler::catchErrors($fnCall);
    }
}
