<?php

namespace Tests\Unit;

use App\Services\LessonRenderer;
use PHPUnit\Framework\TestCase;

class LessonRendererTest extends TestCase
{
    public function test_it_preserves_subscripts_superscripts_and_asterisks_inside_inline_and_block_math(): void
    {
        $renderer = new LessonRenderer();

        $markdown = <<<'MD'
Rumus kecepatan akhir $v_t = v_0 + a * t$ dan energi $E_k = \tfrac{1}{2} m v_1^2 + v_2^2$.

$$s_{total} = v_0\,t + \frac{1}{2}\,a_1 * a_2\,t^2$$

Teks *miring* di luar rumus tetap menjadi italic.
MD;

        $html = $renderer->render($markdown);

        // '_' dan '*' di dalam rumus tidak boleh berubah menjadi <em>...</em>
        $this->assertStringContainsString('$v_t = v_0 + a * t$', $html);
        $this->assertStringContainsString('$E_k = \tfrac{1}{2} m v_1^2 + v_2^2$', $html);
        $this->assertStringContainsString('$$s_{total} = v_0\,t + \frac{1}{2}\,a_1 * a_2\,t^2$$', $html);

        // Markdown biasa tetap ter-render
        $this->assertStringContainsString('<em>miring</em>', $html);
    }

    public function test_it_preserves_fractions_and_latex_backslashes(): void
    {
        $renderer = new LessonRenderer();

        $markdown = 'Tinggi maksimum $h_{maks} = \dfrac{v_0^2}{2g}$ serta $$\frac{\Delta x}{\Delta t} = \bar{v}$$';
        $html = $renderer->render($markdown);

        $this->assertStringContainsString('$h_{maks} = \dfrac{v_0^2}{2g}$', $html);
        $this->assertStringContainsString('$$\frac{\Delta x}{\Delta t} = \bar{v}$$', $html);
    }

    public function test_it_strips_or_escapes_raw_html_and_xss_inside_or_outside_math(): void
    {
        $renderer = new LessonRenderer();

        $markdown = '<script>alert("xss")</script> Halo $<img src=x onerror=alert(1)>$';
        $html = $renderer->render($markdown);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('$&lt;img src=x onerror=alert(1)&gt;$', $html);
    }
}
