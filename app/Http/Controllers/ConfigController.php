<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddConfigRequest;
use App\Models\ConfigItem;
use App\Models\ProductSku;

class ConfigController extends Controller
{
    public function add(AddConfigRequest $request)
    {
        $user = $request->user();
        $skuId  = $request->input('sku_id');
        $amount = $request->input('amount');

        if ($config = $user->configItems()->where('product_sku_id', $skuId)->first()) {
            $config->update([
                'amount' => $config->amount + $amount,
            ]);
        } else {
            $config = new ConfigItem(['amount' => $amount]);
            
            $config->user()->associate($user);
            $config->productSku()->associate($skuId);
            
            $config->save();
        }

        return [];
    }

    public function remove(ProductSku $sku, Request $request) {
        $request->user()->configItems()->where('product_sku_id', $sku->id)->delete();

        return [];
    }

    public function removeAll(Request $request) {
        $request->user()->configItems()->delete();

        return [];
    }
}
