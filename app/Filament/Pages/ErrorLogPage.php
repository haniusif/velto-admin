<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/**
 * Reads the Laravel log files straight off disk and shows the recent entries,
 * errors first. There is no log viewer on this host and SSH is not always to
 * hand, so this is the fastest way to see what the server is complaining about.
 *
 * It only ever reads: no entry is written, edited or deleted from here.
 */
class ErrorLogPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'error-log';

    protected string $view = 'filament.pages.error-log';

    /** The log file currently being viewed (basename within storage/logs). */
    public string $file = '';

    /** Which levels to show: 'error' (and above), 'warning' (and above) or 'all'. */
    public string $level = 'error';

    /** How many matching entries to show at most (newest first). */
    public int $limit = 200;

    /** Levels Monolog can emit, most severe first. */
    private const LEVELS = [
        'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG',
    ];

    public static function getNavigationLabel(): string
    {
        return __('Error log');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Error log');
    }

    public function mount(): void
    {
        // Default to the newest file on screen.
        $files = $this->files();
        $this->file = array_key_first($files) ?? '';
    }

    /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('Refresh'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => null),

            Action::make('download')
                ->label(__('Download file'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => $this->file !== '' && is_file($this->path($this->file)))
                ->action(fn () => response()->download($this->path($this->file))),
        ];
    }

    /**
     * The log files on disk, newest first, labelled with their date and size.
     *
     * @return array<string, string>  basename => label
     */
    public function files(): array
    {
        $dir = storage_path('logs');

        if (! is_dir($dir)) {
            return [];
        }

        $paths = glob($dir.'/*.log') ?: [];

        // Newest first by modified time.
        usort($paths, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        $out = [];
        foreach ($paths as $p) {
            $name = basename($p);
            $out[$name] = $name.'  ·  '.$this->humanSize((int) filesize($p));
        }

        return $out;
    }

    /** Options for the level selector. */
    public function levelOptions(): array
    {
        return [
            'error' => __('Errors only'),
            'warning' => __('Errors & warnings'),
            'all' => __('All levels'),
        ];
    }

    /**
     * Parse the selected file into entries, newest first, filtered by level.
     *
     * @return array<int, array{time:string, level:string, message:string, detail:string, color:string}>
     */
    public function entries(): array
    {
        if ($this->file === '') {
            return [];
        }

        $path = $this->path($this->file);
        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        $text = $this->readTail($path);

        // Split on the start of each entry: "[2026-11-24 17:11:17] env.LEVEL: ".
        // A continuation line (stack trace, context) belongs to the entry above.
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})[^\]]*\]\s+[\w\-]+\.([A-Z]+):\s?(.*)$/';

        $entries = [];
        $current = null;

        foreach (preg_split("/\r\n|\n|\r/", $text) as $line) {
            if (preg_match($pattern, $line, $m)) {
                if ($current !== null) {
                    $entries[] = $current;
                }
                $current = [
                    'time' => $m[1],
                    'level' => $m[2],
                    'message' => trim($m[3]),
                    'detail' => '',
                ];
            } elseif ($current !== null) {
                $current['detail'] .= ($current['detail'] === '' ? '' : "\n").$line;
            }
        }
        if ($current !== null) {
            $entries[] = $current;
        }

        $allowed = $this->allowedLevels();

        $entries = array_values(array_filter(
            $entries,
            fn ($e) => in_array($e['level'], $allowed, true),
        ));

        // Newest first, capped.
        $entries = array_reverse($entries);
        $entries = array_slice($entries, 0, $this->limit);

        return array_map(function ($e) {
            $e['detail'] = rtrim($e['detail']);
            $e['message'] = $this->summarise($e['message']);
            $e['color'] = $this->color($e['level']);

            return $e;
        }, $entries);
    }

    /** The levels kept for the current filter. */
    private function allowedLevels(): array
    {
        return match ($this->level) {
            'all' => self::LEVELS,
            'warning' => ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING'],
            default => ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR'],
        };
    }

    /** Trim a runaway one-liner so the table stays readable; detail keeps the rest. */
    private function summarise(string $message): string
    {
        $firstLine = Str::of($message)->explode("\n")->first() ?? '';

        return (string) Str::limit(trim($firstLine), 300);
    }

    private function color(string $level): string
    {
        return match ($level) {
            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'danger',
            'WARNING' => 'warning',
            'NOTICE', 'INFO' => 'info',
            default => 'gray',
        };
    }

    private function path(string $name): string
    {
        // Guard against path traversal — only a bare basename is ever opened.
        return storage_path('logs/'.basename($name));
    }

    /**
     * Read the whole file when small, otherwise just the tail, so a multi-MB
     * log never has to be loaded in full to show its newest entries.
     */
    private function readTail(string $path): string
    {
        $cap = 3 * 1024 * 1024; // 3 MB is plenty for the recent tail.
        $size = (int) filesize($path);

        if ($size <= $cap) {
            return (string) file_get_contents($path);
        }

        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return '';
        }
        fseek($fh, -$cap, SEEK_END);
        $text = (string) stream_get_contents($fh);
        fclose($fh);

        // Drop the partial first line we landed in the middle of.
        $nl = strpos($text, "\n");

        return $nl === false ? $text : substr($text, $nl + 1);
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024).' KB';
        }

        return $bytes.' B';
    }
}
