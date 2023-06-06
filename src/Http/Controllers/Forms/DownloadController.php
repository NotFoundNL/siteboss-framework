<?php

namespace NotFound\Framework\Http\Controllers\Forms;

use Illuminate\Support\Facades\Storage;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Forms\Data;
use NotFound\Framework\Models\Forms\Field;

class DownloadController extends Controller
{
    public function CheckFile($formId, $fieldId, $dataId, $arrayIndex)
    {
        $file = $this->getFile($fieldId, $dataId, $arrayIndex);
        $mimeType = $file->mime;

        $field = Field::where('id', $fieldId)->firstOrFail();
        $filetypeType = $field->properties->filetypes;
        $mimeTypeConverter = new \NotFound\Framework\Services\Forms\MimetypeConverter();
        $acceptedFiletypes = $mimeTypeConverter->getMimetype($filetypeType);

        if (! in_array($mimeType, $acceptedFiletypes)) {
            return response()->json(['message' => false], 401);
        }

        return response()->json(['message' => true], 200);
    }

    public function downloadFile($formId, $fieldId, $dataId, $arrayIndex)
    {
        $file = $this->getFile($fieldId, $dataId, $arrayIndex);

        if (Storage::disk('formbuilder')->exists($file->loc)) {
            return Storage::disk('formbuilder')->download($file->loc, $file->filename);
        }

        return response()->json(['file not found on server'], 404);
    }

    public function unauthenticatedDownload($submitId, $fieldId, $UUID)
    {
        // BUG: RIGHTS: Not always allowed for unauthenticated users
        $data = Data::where('id', $submitId)->first();
        if (! $data) {
            abort(403, 'e0');
        }

        if (! property_exists($data->data, $fieldId)) {
            abort(403, 'e1');
        }

        $field = $data->data->{$fieldId};
        foreach ($field->value as $file) {
            if (! isset($file->uuid) || $file->uuid != $UUID) {
                continue;
            }

            if (Storage::disk('formbuilder')->exists($file->loc)) {
                return Storage::disk('formbuilder')->download($file->loc, $file->filename);
            }
        }

        abort(403, 'e2');
    }

    public function downloadReportFilled($id)
    {
        $this->downloadReport($id, 'filled');
    }

    public function downloadReportAll($id)
    {
        $this->downloadReport($id, 'all');
    }

    public function downloadReport($id, $type = 'normal')
    {
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=report.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];

        $dataHandler = new \NotFound\Framework\Services\Forms\UserDataTransformer($id, $type);
        $list = $dataHandler->getDataCsv();

        $callback = function () use ($list) {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getFile($fieldId, $dataId, $arrayIndex)
    {
        $dataContainer = Data::where('id', $dataId)->first();
        $field = $dataContainer->data->$fieldId;
        // array index added 1, since it doesn't accept 0 as a paramter
        return $field->value[$arrayIndex - 1];
    }
}
