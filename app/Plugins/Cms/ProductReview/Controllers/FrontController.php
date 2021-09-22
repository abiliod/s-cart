<?php
#App\Plugins\Cms\ProductReview\Controllers\FrontController.php
namespace App\Plugins\Cms\ProductReview\Controllers;

use App\Plugins\Cms\ProductReview\AppConfig;
use App\Http\Controllers\RootFrontController;
use App\Plugins\Cms\ProductReview\Models\PluginModel;
use Validator;
class FrontController extends RootFrontController
{
    public $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = new AppConfig;
    }

    public function postReview() {

        if (!auth()->user()) {
            return redirect()->guest(sc_route('login'));
        }
        $data = request()->all();
        $validate = [
            'product_id' => 'required',
            'comment' => 'required|string|max:300|min:10',
            'point' => 'required|numeric|min:1|max:5',
        ];
        if(sc_captcha_method() && in_array('checkout', sc_captcha_page())) {
            $data['captcha_field'] = $data[sc_captcha_method()->getField()] ?? '';
            $validate['captcha_field'] = ['required', 'string', new \SCart\Core\Rules\CaptchaRule];
        }
        $validator = Validator::make($data, $validate);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }

        $dataInsert = [
            'product_id' => $data['product_id'],
            'customer_id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'comment' => strip_tags(str_replace("\n", "<br>", $data['comment']), '<br>'),
            'point' => min((int)$data['point'], 5),
            'status' => sc_config_global('ProductReview_status_default'),
        ];

        $dataInsert = sc_clean($dataInsert);
        PluginModel::create($dataInsert);
        return redirect()->back()->with('success', trans($this->plugin->pathPlugin.'::lang.submit_success'));
    }

    /**
     * Remove review
     * @return [type] [description]
     */
    public function removeReview()
    {
        if (!auth()->user()) {
            return redirect()->guest(sc_route('login'));
        }
        $data = request()->all();
        $id = $data['id'];
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
        } else {
            $uID = auth()->user()->id;
            $review = PluginModel::where('id', $id)->where('customer_id', $uID)->first();
            if ($review) {
                $review->delete();
                return response()->json(['error' => 0, 'msg' => 'OK']);
            } else {
                return response()->json(['error' => 1, 'msg' => 'Access denied']);
            }
        }

    } 

}
