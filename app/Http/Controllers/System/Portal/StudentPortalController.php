<?php

namespace App\Http\Controllers\System\Portal;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\Portals\StudentPortalOverviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function trainings(): View
    {
        return view('pages.app.portal.student.trainings.index');
    }

    public function show(Training $training): View
    {
        return view('pages.app.portal.student.trainings.show', [
            'training' => $training,
        ]);
    }

    public function history(Request $request, StudentPortalOverviewService $overviewService): View
    {
        return view('pages.app.portal.student.history', [
            'overview' => $overviewService->build($request->user()),
        ]);
    }

    public function receipts(Request $request, StudentPortalOverviewService $overviewService): View
    {
        return view('pages.app.portal.student.receipts', [
            'overview' => $overviewService->build($request->user()),
        ]);
    }

    public function certificates(Request $request, StudentPortalOverviewService $overviewService): View
    {
        return view('pages.app.portal.student.certificates', [
            'overview' => $overviewService->build($request->user()),
        ]);
    }
}
