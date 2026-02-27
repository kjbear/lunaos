@extends('components.layouts.app')

@section('title', 'Documentation')

@push('head')
<style>
.markdown-content {
    color: #a0a0b8;
    line-height: 1.7;
}
.markdown-content h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #e4e4f0;
    margin-bottom: 1rem;
    margin-top: 1.5rem;
}
.markdown-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #e4e4f0;
    margin-top: 2rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #2a2a40;
    padding-bottom: 0.5rem;
}
.markdown-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #e4e4f0;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}
.markdown-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #e4e4f0;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}
.markdown-content p {
    margin-bottom: 1rem;
}
.markdown-content ul, .markdown-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}
.markdown-content li {
    margin-bottom: 0.5rem;
}
.markdown-content a {
    color: #a78bfa;
    text-decoration: none;
}
.markdown-content a:hover {
    text-decoration: underline;
}
.markdown-content code {
    background: #1f1f35;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-family: 'JetBrains Mono', monospace;
    color: #e4e4f0;
}
.markdown-content pre {
    background: #0f0f1a;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    border: 1px solid #2a2a40;
    margin-bottom: 1rem;
}
.markdown-content pre code {
    background: none;
    padding: 0;
    font-size: 0.875rem;
}
.markdown-content blockquote {
    border-left: 4px solid #7c3aed;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #6b6b80;
    font-style: italic;
}
.markdown-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}
.markdown-content th, .markdown-content td {
    border: 1px solid #2a2a40;
    padding: 0.5rem 0.75rem;
    text-align: left;
}
.markdown-content th {
    background: #1f1f35;
    font-weight: 600;
    color: #e4e4f0;
}
.markdown-content img {
    max-width: 100%;
    border-radius: 0.5rem;
    margin: 1rem 0;
}
.markdown-content hr {
    border: none;
    border-top: 1px solid #2a2a40;
    margin: 2rem 0;
}
.markdown-content strong {
    font-weight: 600;
    color: #e4e4f0;
}
.markdown-content em {
    font-style: italic;
}
</style>
@endpush

@section('content')
<livewire:docs-viewer />
@endsection
