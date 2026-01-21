<?php

namespace App\View\Components\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Guest extends Component
{
    public ?string $fullTitle = null;
    public ?string $metaDescription = null;
    public ?string $metaKeywords = null;
    public ?string $canonicalUrl = null;
    public ?string $robotsContent = null;
    public ?string $ogImg = null;
    public ?string $twImg = null;
    public ?string $appName = null;
    public ?string $ogType = null;
    public ?string $locale = null;
    public ?string $twCard = null;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $canonical = null,
        ?string $robots = null,
        ?string $ogImage = null,
        string $ogType = 'web',
        string $locale = 'pt_BR',
        ?string $twImage = null,
        string $twCard = 'summary_large_image'
    ) {
        $this->appName = config('app.name', 'Evangelismo Explosivo');

        // Título completo: usa o título passado ou o nome do app
        $pageTitle = $title ? trim($title) : $this->appName;
        $this->fullTitle = $title ? ($pageTitle . ' 🌎 ' . $this->appName) : $this->appName;

        // Metadados com valores padrão
        $this->metaDescription = $description
            ?? 'Evangelismo Explosivo (EE) no Brasil: ministério que capacita igrejas a evangelizar através de amizades, discipular novos crentes e multiplicar líderes.';
        $this->metaKeywords = $keywords ?? 'evangelismo, discipulado, treinamento, evangelismo explosivo';
        $this->canonicalUrl = $canonical ?? url()->current();
        $this->robotsContent = $robots ?? 'index,follow';

        // Imagens de fallback
        $this->ogImg = $ogImage ?? asset('images/og/default.webp');
        $this->twImg = $twImage ?? $this->ogImg;

        // Outros campos
        $this->ogType = $ogType;
        $this->locale = $locale;
        $this->twCard = $twCard;
    }

    public function render(): View|Closure|string
    {
        return view('components.layouts.guest');
    }
}
