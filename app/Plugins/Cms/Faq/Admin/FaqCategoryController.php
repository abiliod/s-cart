<?php
namespace App\Plugins\Cms\Faq\Admin;

use App\Http\Controllers\RootAdminController;
use SCart\Core\Front\Models\ShopLanguage;
use App\Plugins\Cms\Faq\Admin\Models\AdminFaqCategory;
use App\Plugins\Cms\Faq\AppConfig;

use Validator;

class FaqCategoryController extends RootAdminController
{
    public $languages;
    public $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->languages = ShopLanguage::getListActive();
        $this->plugin = new AppConfig;

    }

    public function index()
    {
        $categoriesTitle =  AdminFaqCategory::getListTitleAdmin();

        $data = [
            'title' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.list'),
            'subTitle' => '',
            'icon' => 'fa fa-indent',
            'menuRight' => [],
            'menuLeft' => [],
            'topMenuRight' => [],
            'topMenuLeft' => [],
            'urlDeleteItem' => sc_route_admin('admin_faq_category.delete'),
            'removeList' => 1, // 1 - Enable function delete list item
            'buttonRefresh' => 1, // 1 - Enable button refresh
            'buttonSort' => 1, // 1 - Enable button sort
            'css' => '', 
            'js' => '',
        ];

        $listTh = [
            'id' => sc_language_render($this->plugin->pathPlugin.'::Category.id'),
            'title' => sc_language_render($this->plugin->pathPlugin.'::Category.title'),
            'status' => sc_language_render($this->plugin->pathPlugin.'::Category.status'),
            'sort' => sc_language_render($this->plugin->pathPlugin.'::Category.sort'),
            'action' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.action'),
        ];
        $sort_order = sc_clean(request('sort_order') ?? 'id_desc');
        $keyword    = sc_clean(request('keyword') ?? '');
        $arrSort = [
            'id__desc' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.sort_order.id_desc'),
            'id__asc' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.sort_order.id_asc'),
            'title__desc' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.sort_order.title_desc'),
            'title__asc' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.sort_order.title_asc'),
        ];

        $dataSearch = [
            'keyword'    => $keyword,
            'sort_order' => $sort_order,
            'arrSort'    => $arrSort,
        ];
        $dataTmp = (new AdminFaqCategory)->getCategoryListAdmin($dataSearch);

        $dataTr = [];
        foreach ($dataTmp as $key => $row) {
            $dataTr[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'status' => $row['status'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'sort' => $row['sort'],
                'action' => '
                    <a href="' . sc_route_admin('admin_faq_category.edit', ['id' => $row['id']]) . '"><span title="' . sc_language_render($this->plugin->pathPlugin.'::Category.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
                    <span onclick="deleteItem(' . $row['id'] . ');"  title="' . sc_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fa fa-trash"></i></span>
                    '
                ,
            ];
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links($this->templatePathAdmin.'component.pagination');
        $data['resultItems'] = sc_language_render($this->plugin->pathPlugin.'::Category.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);


        //menuRight
        $data['menuRight'][] = '<a href="' . sc_route_admin('admin_faq_category.create') . '" class="btn  btn-success  btn-flat" title="New" id="button_create_new">
                           <i class="fa fa-plus"></i><span class="hidden-xs">' . sc_language_render('action.add') . '</span>
                           </a>';
        //=menuRight

        //menuSort
        $optionSort = '';
        foreach ($arrSort as $key => $status) {
            $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
        }
        $data['urlSort'] = sc_route_admin('admin_faq_category.index', request()->except(['_token', '_pjax', 'sort_order']));

        $data['optionSort'] = $optionSort;
        //=menuSort

        //menuSearch
        $data['topMenuRight'][] = '
                <form action="' . sc_route_admin('admin_faq_category.index') . '" id="button_search">
                <div class="input-group input-group" style="width: 250px;">
                    <input type="text" name="keyword" class="form-control float-right" placeholder="' . sc_language_render($this->plugin->pathPlugin.'::Category.admin.search_place') . '" value="' . $keyword . '">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                </form>';
        //=menuSearch


        return view($this->templatePathAdmin.'screen.list')
            ->with($data);
    }

/**
 * Form create new order in admin
 * @return [type] [description]
 */
    public function create()
    {
        $data = [
            'title' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.add_new_title'),
            'subTitle' => '',
            'title_description' => sc_language_render($this->plugin->pathPlugin.'::Category.admin.add_new_des'),
            'icon' => 'fa fa-plus',
            'languages' => $this->languages,
            'category' => [],
            'categories' => (new AdminFaqCategory)->getTreeCategoriesAdmin(),
            'url_action' => sc_route_admin('admin_faq_category.create'),
            'pathPlugin' => $this->plugin->pathPlugin,
        ];
        return view($this->plugin->pathPlugin.'::Admin.faq_category')
            ->with($data);
    }

/**
 * Post create new order in admin
 * @return [type] [description]
 */
    public function postCreate()
    {
        $data = request()->all();

        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['title'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        $validator = Validator::make($data, [
            'sort' => 'numeric|min:0',
            'descriptions.*.title' => 'required|string|max:200',
            'descriptions.*.keyword' => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:100',
        ], [
            'alias.regex' => sc_language_render($this->plugin->pathPlugin.'::Category.alias_validate'),
            'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render($this->plugin->pathPlugin.'::Category.title')]),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        $dataInsert = [
            'image'    => $data['image'],
            'alias'    => $data['alias'],
            'status'   => !empty($data['status']) ? 1 : 0,
            'sort'     => (int) $data['sort'],
            'store_id' => session('adminStoreId'),
        ];

        $category = AdminFaqCategory::createCategoryAdmin($dataInsert);
        $id = $category->id;
        $dataDes = [];
        $languages = $this->languages;
        foreach ($languages as $code => $value) {
            $dataDes[] = [
                'category_id' => $id,
                'lang'        => $code,
                'title'       => $data['descriptions'][$code]['title'],
                'keyword'     => $data['descriptions'][$code]['keyword'],
                'description' => $data['descriptions'][$code]['description'],
            ];
        }
        AdminFaqCategory::insertDescriptionAdmin($dataDes);

        sc_clear_cache('cache_faq_category');

        return redirect()->route('admin_faq_category.index')->with('success', sc_language_render($this->plugin->pathPlugin.'::Category.admin.create_success'));

    }

/**
 * Form edit
 */
    public function edit($id)
    {
        $category = AdminFaqCategory::getCategoryAdmin($id);

        if (!$category) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $data = [
            'title'             => sc_language_render($this->plugin->pathPlugin.'::Category.admin.edit'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-pencil-square-o',
            'languages'         => $this->languages,
            'category'          => $category,
            'categories'        => (new AdminFaqCategory)->getTreeCategoriesAdmin(),
            'url_action'        => sc_route_admin('admin_faq_category.edit', ['id' => $category['id']]),
            'pathPlugin'        => $this->plugin->pathPlugin,
        ];
        return view($this->plugin->pathPlugin.'::Admin.faq_category')
            ->with($data);
    }

/**
 * update status
 */
    public function postEdit($id)
    {
        $category = AdminFaqCategory::getCategoryAdmin($id);
        if (!$category) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $data = request()->all();

        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['title'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        $validator = Validator::make($data, [
            'sort'                       => 'numeric|min:0',
            'descriptions.*.title'       => 'required|string|max:200',
            'descriptions.*.keyword'     => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            'alias'                      => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:100',
        ], [
            'alias.regex' => sc_language_render($this->plugin->pathPlugin.'::Category.alias_validate'),
            'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render($this->plugin->pathPlugin.'::Category.title')]),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        //Edit
        $dataUpdate = [
            'image'    => $data['image'],
            'alias'    => $data['alias'],
            'sort'     => $data['sort'],
            'status'   => empty($data['status']) ? 0 : 1,
            'store_id' => session('adminStoreId'),
        ];

        $category->update($dataUpdate);
        $category->descriptions()->delete();
        $dataDes = [];
        foreach ($data['descriptions'] as $code => $row) {
            $dataDes[] = [
                'category_id' => $id,
                'lang'        => $code,
                'title'       => $row['title'],
                'keyword'     => $row['keyword'],
                'description' => $row['description'],
            ];
        }

        AdminFaqCategory::insertDescriptionAdmin($dataDes);

        sc_clear_cache('cache_faq_category');

        return redirect()->route('admin_faq_category.index')->with('success', sc_language_render($this->plugin->pathPlugin.'::Category.admin.edit_success'));

    }

    /*
    Delete list Item
    Need mothod destroy to boot deleting in model
    */
    public function deleteList()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            $arrDontPermission = [];
            foreach ($arrID as $key => $id) {
                if(!$this->checkPermisisonItem($id)) {
                    $arrDontPermission[] = $id;
                }
            }
            if (count($arrDontPermission)) {
                return response()->json(['error' => 1, 'msg' => sc_language_render('admin.remove_dont_permisison') . ': ' . json_encode($arrDontPermission)]);
            }
            AdminFaqCategory::destroy($arrID);
            sc_clear_cache('cache_faq_category');
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

    /**
     * Check permisison item
     */
    public function checkPermisisonItem($id) {
        return AdminFaqCategory::getCategoryAdmin($id);
    }

}
