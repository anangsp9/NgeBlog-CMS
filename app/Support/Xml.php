<?php

namespace App\Support;

use Illuminate\Http\Response;

class Xml
{
    /**
     * Deklarasi XML tidak boleh ditulis langsung di dalam template Blade.
     * Bila short_open_tag aktif, `<?xml` akan dianggap PHP oleh parser.
     */
    public const DECLARATION = '<?xml version="1.0" encoding="UTF-8"?>';

    public static function response(string $body, string $contentType = 'application/xml'): Response
    {
        return response(self::DECLARATION."\n".trim($body), 200, [
            'Content-Type' => $contentType.'; charset=UTF-8',
        ]);
    }
}
