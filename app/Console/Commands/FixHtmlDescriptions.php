<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Repairs product `html_description` values that an earlier rich-text editor
 * mangled: it HTML-encoded the markup (`<` -> `&lt;`) and wrapped each line in
 * a `<p>` tag, so the storefront (which renders the field as raw HTML) shows
 * the code as plain text instead of the intended layout.
 *
 * Only rows that actually look encoded (contain `&lt;`) are touched, so
 * correctly-authored raw HTML is left alone. Run with --dry-run first.
 */
class FixHtmlDescriptions extends Command
{
    protected $signature = 'products:fix-html-description {--dry-run : Show what would change without saving}';

    protected $description = 'Un-mangle product html_description values encoded/wrapped by a previous rich-text editor';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $fixed = 0;

        Product::query()
            ->whereNotNull('html_description')
            ->where('html_description', 'like', '%&lt;%')
            ->each(function (Product $product) use (&$fixed, $dryRun): void {
                $original = (string) $product->html_description;
                $repaired = $this->unmangle($original);

                if ($repaired === $original) {
                    return;
                }

                $fixed++;
                $this->line(($dryRun ? '[dry-run] ' : '')."Fixed product #{$product->id} ({$product->slug})");

                if (! $dryRun) {
                    $product->forceFill(['html_description' => $repaired])->save();
                }
            });

        $this->info(($dryRun ? '[dry-run] ' : '')."Done. Affected products: {$fixed}");

        return self::SUCCESS;
    }

    /**
     * Strip the editor's per-line <p> wrapping and decode HTML entities so the
     * value becomes the raw HTML it was before the editor touched it.
     */
    private function unmangle(string $value): string
    {
        // </p><p> boundaries were line breaks in the original source.
        $value = preg_replace('#</p>\s*<p>#i', "\n", $value);
        // Remove any remaining paragraph tags the editor added.
        $value = preg_replace('#</?p>#i', '', (string) $value);
        // Decode &lt; &gt; &amp; &#039; etc. back to real characters.
        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5);
    }
}
