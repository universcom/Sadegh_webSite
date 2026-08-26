<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Controllers\Controller;
use App\Core\Request;

final class NotFoundController extends Controller
{
    public function show(Request $request): never
    {
        $this->notFound();
    }
}
