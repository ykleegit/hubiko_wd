<?php

namespace Hubiko\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $table = 'ticket_custom_fields';
    protected $fillable = [
        'name',
        'type',
        'module',
        'created_by',
        'workspace',
        'field_value',
    ];

    // Scopes for workspace and user filtering
    public function scopeWorkspace($query, $workspace = null)
    {
        $workspace = $workspace ?? getActiveWorkSpace();
        return $query->where('workspace', $workspace);
    }

    public function scopeCreatedBy($query, $createdBy = null)
    {
        $createdBy = $createdBy ?? creatorId();
        return $query->where('created_by', $createdBy);
    }

    public static function saveData($ticket, $data)
    {
        if (!empty($data)) {
            foreach ($data as $fieldId => $value) {
                \App\Models\CustomFieldValue::updateOrCreate(
                    [
                        'record_id' => $ticket->id,
                        'field_id' => $fieldId,
                        'module' => 'ticket',
                    ],
                    [
                        'field_value' => $value,
                        'created_by' => creatorId(),
                        'workspace' => getActiveWorkSpace(),
                    ]
                );
            }
        }
        return true;
    }

    public static function getData($ticket)
    {
        $data = [];
        $values = \App\Models\CustomFieldValue::where('record_id', $ticket->id)
            ->where('module', 'ticket')
            ->get();
        foreach ($values as $value) {
            $data[$value->field_id] = $value->field_value;
        }
        return $data;
    }

    public static function prepareCustomRendering($field, $readonly = false, $model = null)
    {
        $html = '';
        $value = '';
        
        if (!empty($model) && $model->id) {
            $fieldValue = \App\Models\CustomFieldValue::where('record_id', $model->id)
                ->where('field_id', $field->id)
                ->where('module', 'ticket')
                ->first();
            if ($fieldValue) {
                $value = $fieldValue->field_value;
            }
        }
        
        switch ($field->type) {
            case 'text':
                $html = self::renderTextField($field, $value, $readonly);
                break;
            case 'email':
                $html = self::renderEmailField($field, $value, $readonly);
                break;
            case 'number':
                $html = self::renderNumberField($field, $value, $readonly);
                break;
            case 'date':
                $html = self::renderDateField($field, $value, $readonly);
                break;
            case 'textarea':
                $html = self::renderTextareaField($field, $value, $readonly);
                break;
            case 'select':
                $html = self::renderSelectField($field, $value, $readonly);
                break;
            case 'radio':
                $html = self::renderRadioField($field, $value, $readonly);
                break;
            case 'checkbox':
                $html = self::renderCheckboxField($field, $value, $readonly);
                break;
            default:
                $html = '';
        }
        
        return $html;
    }

    private static function renderTextField($field, $value, $readonly)
    {
        $readonlyAttr = $readonly ? 'readonly' : '';
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                <input type="text" name="customField[' . $field->id . ']" class="form-control" value="' . $value . '" ' . $readonlyAttr . '>
            </div>';
    }
    
    private static function renderEmailField($field, $value, $readonly)
    {
        $readonlyAttr = $readonly ? 'readonly' : '';
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                <input type="email" name="customField[' . $field->id . ']" class="form-control" value="' . $value . '" ' . $readonlyAttr . '>
            </div>';
    }
    
    private static function renderNumberField($field, $value, $readonly)
    {
        $readonlyAttr = $readonly ? 'readonly' : '';
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                <input type="number" name="customField[' . $field->id . ']" class="form-control" value="' . $value . '" ' . $readonlyAttr . '>
            </div>';
    }
    
    private static function renderDateField($field, $value, $readonly)
    {
        $readonlyAttr = $readonly ? 'readonly' : '';
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                <input type="date" name="customField[' . $field->id . ']" class="form-control" value="' . $value . '" ' . $readonlyAttr . '>
            </div>';
    }
    
    private static function renderTextareaField($field, $value, $readonly)
    {
        $readonlyAttr = $readonly ? 'readonly' : '';
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                <textarea name="customField[' . $field->id . ']" class="form-control" ' . $readonlyAttr . '>' . $value . '</textarea>
            </div>';
    }
    
    private static function renderSelectField($field, $value, $readonly)
    {
        $options = explode(',', $field->field_value);
        $optionsHtml = '';
        foreach ($options as $option) {
            $selected = ($value == $option) ? 'selected' : '';
            $optionsHtml .= '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
        }
        
        $disabledAttr = $readonly ? 'disabled' : '';
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                <select name="customField[' . $field->id . ']" class="form-control" ' . $disabledAttr . '>
                    <option value="">Select ' . $field->name . '</option>
                    ' . $optionsHtml . '
                </select>
            </div>';
    }
    
    private static function renderRadioField($field, $value, $readonly)
    {
        $options = explode(',', $field->field_value);
        $radioHtml = '';
        foreach ($options as $option) {
            $checked = ($value == $option) ? 'checked' : '';
            $disabledAttr = $readonly ? 'disabled' : '';
            $radioHtml .= '<div class="form-check">
                <input type="radio" name="customField[' . $field->id . ']" value="' . $option . '" ' . $checked . ' ' . $disabledAttr . ' class="form-check-input">
                <label class="form-check-label">' . $option . '</label>
            </div>';
        }
        
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                ' . $radioHtml . '
            </div>';
    }
    
    private static function renderCheckboxField($field, $value, $readonly)
    {
        $options = explode(',', $field->field_value);
        $values = explode(',', $value);
        $checkboxHtml = '';
        
        foreach ($options as $option) {
            $checked = in_array($option, $values) ? 'checked' : '';
            $disabledAttr = $readonly ? 'disabled' : '';
            $checkboxHtml .= '<div class="form-check">
                <input type="checkbox" name="customField[' . $field->id . '][]" value="' . $option . '" ' . $checked . ' ' . $disabledAttr . ' class="form-check-input">
                <label class="form-check-label">' . $option . '</label>
            </div>';
        }
        
        return '<div class="form-group col-md-12">
                <label>' . $field->name . '</label>
                ' . $checkboxHtml . '
            </div>';
    }
} 