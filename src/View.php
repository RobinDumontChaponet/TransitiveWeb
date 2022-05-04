<?php

namespace Transitive\Web;

use Transitive\Core;
use Transitive\Simple;

class View extends Simple\View implements Core\View
{
    public function __construct(
		/**
		 * styles tags and linked scripts.
		 */
		public array $styles = [],
		/**
		 * scripts tags and linked scripts.
		 */
		public array $scripts = [],
		/**
		 * metas tags.
		 */
		public array $metas = [],
	)
    {
        parent::__construct();
    }

    public function getTitle(string $prefix = '', string $separator = ' | ', string $sufix = ''): string
    {
        return parent::getTitle('<title>'.$prefix, $separator, $sufix.'</title>');
    }

    public function getHead(): Core\ViewResource
    {
        return new Core\ViewResource(array(
            'metas' => $this->getMetasValue(),
            'title' => $this->getTitleValue(),
            'scripts' => $this->getScriptsValue(),
            'styles' => $this->getStylesValue(),
        ), 'asArray');
    }

    public function getHeadValue(): string
    {
        return '<head><meta charset="UTF-8">'
               .$this->getHead()->asString()
               .'</head>';
    }

    public function __debugInfo()
    {
        return array(
            'title' => $this->title,
            'metas' => $this->metas,
            'scripts' => $this->scripts,
            'styles' => $this->styles,
            'content' => $this->getContent(),
            'data' => $this->getData(),
        );
    }

    public function addRawMetaTag(string $rawTag): void
    {
        $this->metas[] = array(
            'raw' => $rawTag,
        );
    }

    public function addMetaTag(string $name, string $content = ''): void
    {
        $this->metas[] = array(
            'name' => $name,
            'content' => $content,
        );
    }

    public function getMetasValue(): array
    {
        return $this->metas;
    }

    public function getMetas(): string
    {
        $str = '';

        if(isset($this->metas))
            foreach($this->metas as $meta)
                if(isset($meta['name']))
                    $str .= '<meta name="'.$meta['name'].'" content="'.$meta['content'].'">';
                else
                    $str .= $meta['raw'];

        return $str;
    }

    public function addStyle(string $content, string $type = 'text/css'): void
    {
        $this->styles[] = array(
            'type' => $type,
            'content' => $content,
        );
    }

    public function addScript(string $content, string $type = 'text/javascript'): void
    {
        $this->scripts[] = array(
            'type' => $type,
            'content' => $content,
        );
    }

    public function linkStyleSheet(string $href, string $type = 'text/css', bool $defer = false, bool $cacheBust = true, string $rel = 'stylesheet'): void
    {
        if($cacheBust)
            $href = self::cacheBust($href);
        $this->styles[] = array(
            'href' => $href,
            'type' => $type,
            'defer' => $defer,
            'rel' => $rel,
        );
    }

    public function linkScript(string $href, string $type = 'text/javascript', bool $defer = false, bool $cacheBust = true): void
    {
        if($cacheBust)
            $href = self::cacheBust($href);
        $this->scripts[] = array(
            'href' => $href,
            'type' => $type,
            'defer' => $defer,
        );
    }

    public function addRawStyleTag(string $rawTag): void
    {
        $this->styles[] = array(
            'raw' => $rawTag,
        );
    }

    public function addRawScriptTag(string $rawTag): void
    {
        $this->scripts[] = array(
            'raw' => $rawTag,
        );
    }

    public function importStyleSheet(string $filepath, string $type = 'text/css', bool $cacheBust = false): bool
    {
        if(!is_file($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource not found or not a file', E_USER_NOTICE);

            return false;
        }
        if(!is_readable($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource is not readable', E_USER_NOTICE);

            return false;
        }

        if($cacheBust)
            $filepath = self::cacheBust($filepath);
        $this->addStyle(self::_getIncludeContents($filepath), $type);

        return true;
    }

    public function importScript(string $filepath, string $type = 'text/javascript', bool $cacheBust = false): bool
    {
        if(!is_file($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource not found or not a file', E_USER_NOTICE);

            return false;
        }
        if(!is_readable($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource is not readable', E_USER_NOTICE);

            return false;
        }

        if($cacheBust)
            $filepath = self::cacheBust($filepath);
        $this->addScript(self::_getIncludeContents($filepath), $type);

        return true;
    }

    public function getStyles(): string
    {
        $str = '';

        if(isset($this->styles))
            foreach($this->styles as $style)
                if(isset($style['content']))
                    $str .= '<style type="'.$style['type'].'">'.$style['content'].'</style>';
                elseif(isset($style['href']))
                    $str .= '<link rel="'.$style['rel'].'" type="'.$style['type'].'" href="'.$style['href'].'"'.(($style['defer']) ? ' defer async' : '').' />';
                elseif(isset($style['raw']))
                    $str .= $style['raw'];

        return $str;
    }

    public function getStylesContent(): string
    {
        $content = '';
        if(isset($this->styles))
            foreach($this->styles as $style)
                if(isset($style['content']))
                    $content .= $style['content'].PHP_EOL;

        return $content;
    }

    public function getScripts(): string
    {
        $str = '';

        if(isset($this->scripts))
            foreach($this->scripts as $script)
                if(isset($script['content']))
                    $str .= '<script type="'.$script['type'].'">'.$script['content'].'</script>';
                elseif(isset($script['href']))
                    $str .= '<script type="'.$script['type'].'" src="'.$script['href'].'"'.(($script['defer']) ? ' defer async' : '').'></script>';
                elseif(isset($script['raw']))
                    $str .= $script['raw'];

        return $str;
    }

    public function getScriptsContent(): string
    {
        $content = '';
        if(isset($this->scripts))
            foreach($this->scripts as $script)
                if(isset($script['content']))
                    $content .= $script['content'];

        return $content;
    }

    public function getScriptsValue(): array
    {
        return $this->scripts;
    }

    public function getStylesValue(): array
    {
        return $this->styles;
    }

    public function redirect(string $url, int $delay = 0, int $code = 303): bool
    {
        $this->addRawMetaTag('<meta http-equiv="refresh" content="'.$delay.'; url='.$url.'">');

        if(!headers_sent()) {
            http_response_code($code);
            $_SERVER['REDIRECT_STATUS'] = $code;
            if($delay <= 0)
                header('Location: '.$url, true, $code);
            else
                header('Refresh:'.$delay.'; url='.$url, true, $code);

            return true;
        }

        return false;
    }
}
