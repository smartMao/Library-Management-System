<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Session;
use yii\db\Query;
use yii\data\Pagination;
use Yii;

use app\models\ReaderType;



class ReaderTypeController extends Controller
{

	#定义一页面内显示多少条数据条目
	public $defaultPageSize = 10;
	public $defaultPageSize_large = 18;

	
	# 新增条目失败时，提示的内容
	public $addFailTipContent = '新增条目与现有的条目重叠，新增失败';

	   
	# 新增条目成功时, 提示的内容
	public $addTipContent = '添加成功';


	# 删除条目成功时, 提示的内容
	public $delTipContent = '删除成功';


	# 更新条目成功时, 提示的内容
	public $updateTipContent = '更新成功';
	

	public function actionIndex()
	{
		$request = Yii::$app->request;
		if ( $post = $request -> post() ){


			$this -> actionAddReaderType( $post );

		} else {

			$session = new Session;
			$model   = new ReaderType;
			$query  = (new Query) 
				-> select(['PK_readerTypeID', 'readerTypeName', 'readerTypeBorrowNumber']) 
				-> orderBy('PK_readerTypeID DESC') 
				-> from( $model -> tableName() );
			$cloneQuery = clone $query;
		
			$pages = new Pagination(['totalCount' => $cloneQuery->count() ]);
			$pages -> defaultPageSize =  $this -> defaultPageSize;
			$data = $query -> offset( $pages->offset ) 
						   -> limit( $pages->limit ) 
					       -> all();


			$pages_large = new Pagination(['totalCount' => $cloneQuery->count() ]);
			$pages_large -> defaultPageSize =  $this -> defaultPageSize_large;
			$data_large = $query -> offset( $pages_large->offset ) 
						   -> limit( $pages_large->limit ) 
					       -> all();


			return $this->render('index', [
				'model'			=> $model,
				'data'			=> $data,
				'pages'			=> $pages,
				'data_large'	=> $data_large,
				'pages_large'	=> $pages_large,
				'session'		=> $session,

			]);		
		}
	}



	public function actionAddReaderType( $post )
	{
		$session         = new Session;
		$ReaderTypeModel = new ReaderType;

	
		$data = ReaderType::find()->where( ['readerTypeName' => $post['ReaderType']['readerTypeName'] ] ) -> one();

		if( !empty( $data ) ){

			// 输入的 读者类型名称 已存在, 那就不允许保存进数据库了。
			// 那就给个 tip 说明，并返回 index
			$session['tipContent'] = $this->addFailTipContent;	
			$session['isShowTip']  = true;
			return $this->redirect('index');	
		}

	
		$ReaderTypeModel -> readerTypeName         = $post['ReaderType']['readerTypeName'];		
		$ReaderTypeModel -> readerTypeBorrowNumber = $post['ReaderType']['readerTypeBorrowNumber'];		

		if( $ReaderTypeModel -> save() ){
			$session['tipContent'] = $this->addTipContent;
			$session['isShowTip']  = true;

			return $this->redirect(['index']);
		}
	}





	public function actionDelReaderType()
	{

		if(	$id  = Yii::$app->request->get('id') ){
			if( $readerType = ReaderType::findOne( $id) ){
				
				$session = new Session;	
				$session['isShowTip' ] = true;
				$session['tipContent'] = $this->delTipContent;
				
				$readerType -> delete();
				return $this->redirect(['index']);
			} else {

				// 当在数据库中找不到要删除条目的 ID
				return $this->redirect(['index']);
			}	
		}
	}



	/**
	 * 读者类型管理的更新方法
	 */
	public function actionUpdateReaderType()
	{
		$session = Yii::$app->session;
		$request = Yii::$app->request;

		if ( $post = $request->post() ) {

			$readerType = ReaderType::findOne( $post['id'] ); 
			$readerType -> readerTypeName         = $post['ReaderType']['readerTypeName'];
			$readerType -> readerTypeBorrowNumber = $post['ReaderType']['readerTypeBorrowNumber'];

			if( $readerType -> save() ){

				$session['tipContent'] = $this -> updateTipContent;
				$session['isShowTip' ]  = true;			 	
				return $this->redirect(['reader-type/index']);
			}

		} elseif ( $id = $request->get('id') ){

			$readerTypeModel = new ReaderType;
			$readerType      = ReaderType::findOne( $id );		

			return $this->render( 'update', [
				'model' => $readerTypeModel,	
				'data'  => $readerType,
			]);
		} 
		


	}



}



?>
