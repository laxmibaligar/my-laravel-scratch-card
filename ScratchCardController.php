<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScratchCardGift;
use App\Models\ScratchCard;
use App\Models\Employee;

class ScratchCardController extends Controller
{
    
   public function showScratchCard($hash = null)
    {
        if (!$hash) {
            return response()->json(['error' => 'Invalid scratch card URL'], 404);
        }

        $employee = Employee::where('hash', $hash)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $scratchCard = ScratchCard::where('employee_id', $employee->id)
                                // ->where('company_id', $employee->company_id)
                                ->first();
        
        // dd($scratchCard);

        if ($scratchCard) {
          
            $gift = ScratchCardGift::where('id',$scratchCard->gift_id)->first();

            if($gift->id == 13){
                $gift=null;
            }
            $already_scratched = true;

        } else {
            $gift = ScratchCardGift::inRandomOrder()->first();
            $already_scratched = false;
        }

        $data = [
            'employee' => $employee,
            'gift'     => $gift,
            'already_scratched' => $already_scratched,
        ];

        return view('scratchCard.scratchCard', $data);
    }


    public function saveScratchCard(Request $request)
    {
       
        $existing = ScratchCard::where('employee_id', $request->employee_id)
                               ->where('company_id', $request->company_id)
                               ->first();

        if ($existing) {
            return response()->json(['message' => 'Already scratched.'], 200);
        }

        ScratchCard::create([
            'employee_id' => $request->employee_id,
            'company_id'  => $request->company_id,
            'gift_id'     => $request->gift_id,
            'is_done'     => 1,
        ]);

        return response()->json(['message' => 'Scratch card saved successfully.']);
    }
}
