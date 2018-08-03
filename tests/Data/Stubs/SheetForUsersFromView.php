<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Urbics\Laraexcel\Concerns\FromView;
use Urbics\Laraexcel\Concerns\Exportable;

class SheetForUsersFromView implements FromView
{
    use Exportable;

    /**
     * @var Collection
     */
    protected $users;

    /**
     * @param Collection $users
     */
    public function __construct(Collection $users)
    {
        $this->users = $users;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('users', [
            'users' => $this->users,
        ]);
    }
}
