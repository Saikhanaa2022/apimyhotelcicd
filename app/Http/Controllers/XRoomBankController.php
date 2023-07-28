<?php

namespace App\Http\Controllers;

use App\Services\KhanbankService;
use Illuminate\Http\Request;

class XRoomBankController extends Controller
{
    private $khanbankService;
    public function __construct(KhanbankService $khanbankService)
    {
        $this->khanbankService = $khanbankService;
    }


    public function getStatements(Request $request)
    {
        $response = $this->khanbankService->getStatements(1);

        return response()->json($response);
    }
}