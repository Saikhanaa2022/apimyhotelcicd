<?php

namespace App\Http\Controllers;

use App\Models\XRoomConfig;
use App\Models\XRoomReservation;
use App\Models\XRoomRoomTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class XRoomAdminController extends Controller
{
    //

    public function getXRoomReservations(Request $request)
    {
        $query = DB::table('xroom_reservations')
            ->leftJoin('hotels', 'hotels.id', '=', 'xroom_reservations.hotel_id')
            ->leftJoin('room_types', 'room_types.id', '=', 'xroom_reservations.room_type_id')
            ->select([
                'xroom_reservations.id',
                'xroom_reservations.stay_type',
                'xroom_reservations.payment_method',
                'hotels.name as hotelName',
                'room_types.name as roomTypeName',
                'xroom_reservations.code',
                'xroom_reservations.check_in',
                'xroom_reservations.check_out',
                'xroom_reservations.client_id',
                'xroom_reservations.fee',
                'xroom_reservations.amount',
                'xroom_reservations.payment_status',
                'xroom_reservations.created_at',
                'xroom_reservations.updated_at'
            ])
            ->orderBy('xroom_reservations.created_at', 'desc');

        $rowsPerPage = (int) $request->query('rowsPerPage', 12);

        $search = $request->query('search');
        if (!empty($search)) {
            $query->where('code', 'like', '%' . $search . '%')
                ->orWhere('hotels.name', 'like', '%' . $search . '%')
                ->orWhere('room_types.name', 'like', '%' . $search . '%')
                ->orWhere('payment_status', 'like', '%' . $search . '%')
                ->orWhere('stay_type', 'like', '%' . $search . '%')
                ->orWhere('payment_method', 'like', '%' . $search . '%');
        }

        $data = $query->paginate($rowsPerPage);

        return response()->json($data);
    }

    public function getConfigs(Request $request)
    {
        $sortBy = snake_case($request->query('sortBy', 'code'));
        $direction = $request->input('direction');
        $search = $request->input('search');
        $query = XRoomConfig::orderBy($sortBy, $direction);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('desc', 'like', '%' . $search . '%')
                ->orWhere('value', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
        }

        $configs = $query->get();

        return response()->json([
            'data' => $configs,
            'total' => $configs->count()
        ]);
    }

    public function saveConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'name' => 'required|string',
            'value' => 'required',
            'desc' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validate your data',
                    'errors' => $validator->errors()
                ]
            );
        }

        $config = XRoomConfig::find($request->input('code'));
        if (is_null($config)) {
            $config = new XRoomConfig();
        }

        $config->code = $request->code;
        $config->name = $request->name;
        $config->value = $request->value;
        $config->desc = $request->desc;

        $config->save();

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    public function getXRoomTypes(Request $request)
    {
        $rowsPerPage = $request->query('rowsPerPage', 12);

        $search = $request->query('search');

        $query = DB::table('xroom_room_types')
            ->leftJoin('room_types', 'room_types.id', '=', 'xroom_room_types.room_type_id')
            ->leftJoin('hotels', 'hotels.id', '=', 'xroom_room_types.hotel_id')
            ->whereNotNull('hotels.id')
            ->whereNotNull('room_types.id')
            ->select([
                'xroom_room_types.id',
                'room_types.name as roomTypeName',
                'hotels.name as hotelName',
                'room_types.id as roomTypeId',
                'xroom_room_types.manual_order',
            ]);

        if (!empty($search)) {
            $query->where('room_types.name', 'like', '%' . $search . '%')
                ->orWhere('hotels.name', 'like', '%' . $search . '%');
        }

        $data = $query->paginate($rowsPerPage);

        return response()
            ->json($data);
    }

    public function updateMultipleXRoomTypes(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'room_types.*.id' => 'required',
            'room_types.*.manualOrder' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validate your data',
                    'errors' => $validator->errors()
                ],
                400
            );
        }

        $roomTypes = $request->input('room_types');

        foreach ($roomTypes as $ind => $type) {
            XRoomRoomTypes::where('id', $type['id'])->update(['manual_order', $type['manualOrder']]);
        }

        $ids = $request->input('room_types.id');

        $xroomTypes = XRoomRoomTypes::whereIn('id', $ids)->get();

        return response()->json([
            'success' => true,
            'data' => $xroomTypes
        ]);
    }

    public function updateXRoomTypes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'manualOrder' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validate your data',
                    'errors' => $validator->errors()
                ],
                400
            );
        }

        $xroomType = XRoomRoomTypes::find($request->input('id'));

        if ($xroomType == null) {
            return response()->json([
                'success' => false,
                'message' => 'xroom type not found',
            ], 404);
        }

        $xroomType->manual_order = $request->input('manualOrder');
        $xroomType->save();

        return response()->json([
            'success' => true,
            'data' => $xroomType
        ]);
    }
}