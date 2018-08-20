<?php

namespace RefinedDigital\CMS\Modules\Pages\Traits;

use RefinedDigital\CMS\Modules\Core\Models\Uri;
use RefinedDigital\CMS\Modules\Pages\Scopes\IsPageScope;

trait IsPage
{

    public $isPage = true;

    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootIsPage()
    {
        static::addGlobalScope(new IsPageScope);

        static::saved(function($model) {

            // set the title
            $title = null;

            if (request()->has('page.meta')) {
                $request = request()->input('page');
            } else {
                $request = request()->all();
            }

            if(isset($request['meta']['title'])) {
                $title = $request['meta']['title'];
            }
            if (!$title && isset($request['name'])) {
                $title = $request['name'];
            }
            if (!$title && isset($model->meta->title)) {
                $title = $model->meta->title;
            }


            // set the description
            $description = null;
            if(isset($request['meta']['description'])) {
                $description = $request['meta']['description'];
            }

            $template = $model->templateId ?: 1;
            $base = class_basename($model);
            if ($base != 'Page') {
                if (config(strtolower($base).'.details_template_id')) {
                    $template = config(strtolower($base).'.details_template_id');
                }
            } else {
                if ($request['meta']['template_id']) {
                    $template = $request['meta']['template_id'];
                }
            }

            $modelType = get_class($model);

            $uriData = [
                'title'         => $title,
                'name'          => $request['name'],
                'description'   => $description,
                'template_id'   => $template,
                'uriable_id'    => $model->id,
                'uriable_type'  => $modelType,
            ];

            if ($model->meta) {
                $model->meta->fill($uriData);
                $model->meta->save();
                if ($base == 'Page' && $model->meta->uriable_id == 1) {
                    $model->meta->uri = '/';
                    $model->meta->save();
                }
            } else {
                $meta = Uri::create($uriData);
                if ($base == 'Page' && $meta->uriable_id == 1) {
                    $meta->uri = '/';
                    $meta->save();
                }
            }
        });

        static::getModel()->deleted(function($model) {

           if($model->implementsSoftDeletes()) {
                // do the soft deleting
                //$model->meta->delete();
                if ($model->content) {
                    $model->content()->delete();
                }
            } else {
                // do the hard deleting
                //$model->meta->forceDelete();
                if ($model->content) {
                    $model->content()->forceDelete();
                }
            }
        });

    }


    public function implementsSoftDeletes()
    {
        return method_exists($this, 'runSoftDelete');
    }


    public function meta()
    {
        return $this->morphOne('RefinedDigital\CMS\Modules\Core\Models\Uri', 'uriable');
    }

}