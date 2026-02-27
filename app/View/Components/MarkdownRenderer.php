<?php

namespace App\View\Components;

use Illuminate\View\Component;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownRenderer extends Component
{
    public string $content;
    public bool $allowHtml = false;
    
    public function __construct(string $content, bool $allowHtml = false)
    {
        $this->content = $content;
        $this->allowHtml = $allowHtml;
    }
    
    public function render(): string
    {
        $config = [
            'html_input' => $this->allowHtml ? 'allow' : 'strip',
            'allow_unsafe_links' => false,
        ];
        
        $converter = new GithubFlavoredMarkdownConverter($config);
        return $converter->convert($this->content);
    }
}
