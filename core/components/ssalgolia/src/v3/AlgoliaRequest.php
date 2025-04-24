<?php

namespace SSAlgolia\v3;

use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modX;
use MODX\Revolution\modRequest;

class AlgoliaRequest extends modRequest
{
    public function getResource($method, $identifier, array $options = [])
    {
        if (isset($this->modx->resource)) {
            $id = $this->modx->resource->id;
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Algolia ignoring future scans of Resource ID: ' . $id);
            $setting = $this->modx->getObject(modSystemSetting::class, ['key' => 'ssalgolia.skip_scan_resources']);
            if (empty($setting)) {
                $setting = $this->modx->newObject(modSystemSetting::class);
                $setting->set('key', 'ssalgolia.skip_scan_resources');
                $setting->set('namespace', 'ssalgolia');
                $setting->set('area', 'Indexing');
                $setting->set('xtype', 'textfield');
                $setting->set('value', $id);
            }
            $value = $setting->get('value');
            $skip = explode(',', $value);
            if (!in_array($id, $skip)) {
                $skip[] = $id;
            }
            $value = implode(',', $skip);
            $value = trim($value);
            $value = trim($value, ',');
            $setting->set('value', $value);
            $setting->save();
            $this->modx->cacheManager->refresh();
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Resource Request: ' . print_r($method, true) . ' '. print_r($identifier, true));
        $resource = parent::getResource($method, $identifier, $options);
        return $resource;
    }
    public function getHeaders($ucKeys = false)
    {
        return [];
    }
}