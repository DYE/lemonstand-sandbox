<?php

	/**
	 * Allows to paginate records, including database models.
	 * Phpr_Pagination is an general purpose class allowing to paginate a list of records, including database models. 
	 * Usually objects of this class are created automatically with {@link Db_ActiveRecord::paginate()} method.
	 * Below is a typical usage of the pagination object:
	 * <pre>$pagination = $products->paginate(0, 10);</pre>
	 * After obtaining the pagination object you can extract the {@link Phpr_Pagination::getPageCount() number of pages} from this object
	 * and generate pagination markup.
	 * @documentable
	 * @see Db_ActiveRecord
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Phpr_Pagination
	{
		private $_currentPageIndex;
		private $_pageSize;
		private $_rowCount;
		private $_pageCount;

		/**
		 * Creates a new object of Phpr_Pagination class.
		 * @documentable.
		 * @param integer $page_size Specifies the page size (number of records per page).
		 * @return Phpr_Pagination Returns the pagination object.
		 */
		public function __construct( $PageSize = 20 )
		{
			$this->_currentPageIndex = 0;
			$this->_pageSize = $PageSize;
			$this->_rowCount = 0;
			$this->_pageCount = 1;
		}

		/**
		 * Applies pagination to an {@link Db_ActiveRecord} object. 
		 * This method should be called before the model's {@link Db_ActiveRecord::find_all()} method
		 * is called. {@link Db_ActiveRecord} class has a more convenient method for applying
		 * pagination -  {@link Db_ActiveRecord::paginate() paginate()}.
		 * @documentable.
		 * @param Db_ActiveRecord $model Specifies the model object to limit.
		 */
		public function limitActiveRecord( Db_ActiveRecord $Obj )
		{
			$Obj->limit( $this->getPageSize(), $this->getFirstPageRowIndex() );
		}
		
		/**
		 * Restores a pagination object from session or creates a new object.
		 * @param string $Name Specifies a name of the object in the session.
		 * @param integer $PageSize Specifies a page size.
		 */
		public static function fromSession( $Name, $PageSize = 20 )
		{
			if ( !Phpr::$session->has($Name) )
				Phpr::$session[$Name] = new Phpr_Pagination($PageSize);

			return Phpr::$session[$Name];
		}

		/**
		 * Evaluates the number of pages for the page size and row count specified in the object properties.
		 * @return integer
		 */
		private function evaluatePageCount( $PageSize, $RowCount )
		{
			$result = ceil($RowCount/$PageSize);

			if ( $result == 0 )
				$result = 1;

			return $result;
		}

		/**
		 * Re-evaluates the current page index value.
		 * @param integer $CurrentPage Specifies the current page value
		 * @param integer $PageCount Specifies the count value
		 * @return integer
		 */
		private function fixCurrentPageIndex( $CurrentPageIndex, $PageCount )
		{
			$lastPageIndex = $PageCount - 1;

			if ( $CurrentPageIndex > $lastPageIndex )
				$CurrentPageIndex = $lastPageIndex;

			return $CurrentPageIndex;
		}

		/**
		 * Sets a zero-based index of a current page. 
		 * Use this method to switch the pagination to another page before you call
		 * {@link Phpr_Pagination::limitActiveRecord() limitActiveRecord()} method.
		 * The method returns the new page index. This value can differ from the 
		 * passed value if case if the passed value exceeds the maximum number of 
		 * pages or less than zero.
		 * @documentable
		 * @param integer $value Specifies the zero-based page index.
		 * @return integer Returns the new page index. 
		 */
		public function setCurrentPageIndex( $Value )
		{
			$lastPageIndex = $this->_pageCount - 1;

			if ( $Value < 0 )
				$Value = 0;

			if ( $Value > $lastPageIndex )
				$Value = $lastPageIndex;

			$this->_currentPageIndex = $Value;

			return $Value;
		}

		/**
		 * Returns a zero-based index of the current page.
		 * @documentable
		 * @return integer Returns the page index.
		 */
		public function getCurrentPageIndex()
		{
			return $this->_currentPageIndex;
		}

		/**
		 * Sets the number of rows on a single page.
		 * @documentable
		 * @param integer $value Specifies the number of rows on a single page.
		 */
		public function setPageSize( $Value )
		{
			if ( $Value <= 0 )
				throw new Phpr_ApplicationException( "Page size is out of range" );

			$this->_pageSize = $Value;

			$this->_pageCount = $this->evaluatePageCount( $Value, $this->_rowCount );
			$this->_currentPageIndex = $this->fixCurrentPageIndex( $this->_currentPageIndex, $this->_pageCount );
		}

		/**
		 * Returns the number of rows on a single page.
		 * The number of rows (page size) can be set in the object constructor or with
		 * {@link Phpr_Pagination::setPageSize() setPageSize()} method.
		 * @documentable
		 * @return integer Returns the number of rows on a single page.
		 */
		public function getPageSize()
		{
			return $this->_pageSize;
		}

		/**
		 * Sets the total number of rows.
		 * This method should be called before the {@link Phpr_Pagination::limitActiveRecord() limitActiveRecord()} 
		 * method call. If you are working with {@link Db_ActiveRecord} objects, you can obtain
		 * the total number of rows with {@link Db_ActiveRecord::requestRowCount()} method. {@link Db_ActiveRecord::paginate()}
		 * method does this work automatically.
		 * @documentable
		 * @param integer $row_count Specifies the total number of rows.
		 */
		public function setRowCount( $Value )
		{
			if ( $Value < 0 )
				throw new Phpr_ApplicationException( "Row count is out of range" );

			$this->_pageCount = $this->evaluatePageCount( $this->_pageSize, $Value );
			$this->_currentPageIndex = $this->fixCurrentPageIndex( $this->_currentPageIndex, $this->_pageCount );
			$this->_rowCount = $Value;
		}

		/**
		 * Returns the total number of rows.
		 * The total number of rows should be previously set with {@link Phpr_Pagination::setRowCount() setRowCount()} method.
		 * @documentable
		 * @return integer Returns the total number of rows.
		 */
		public function getRowCount()
		{
			return $this->_rowCount;
		}

		/**
		 * Returns an index of the first row on the current page.
		 * @documentable
		 * @return integer Returns the row index.
		 */
		public function getFirstPageRowIndex()
		{
			return $this->_pageSize*$this->_currentPageIndex;
		}
		
		/**
		 * Returns an index of the last row on the current page.
		 * @documentable
		 * @return integer Returns the row index.
		 */
		public function getLastPageRowIndex()
		{
			$index = $this->getFirstPageRowIndex();
			$index += $this->_pageSize-1;

			if ($index > $this->_rowCount-1)
				$index = $this->_rowCount-1;
				
			return $index;
		}

		/**
		 * Determines whether the current page is first.
		 * @documentable
		 * @return boolean Returns TRUE if the current page is first. Returns FALSE otherwise.
		 */
		public function isFirstPage()
		{
			return $this->getCurrentPageIndex() == 0;
		}
		
		/**
		 * Determines whether the current page is last.
		 * @documentable
		 * @return boolean Returns TRUE if the current page is last. Returns FALSE otherwise.
		 */
		public function isLastPage()
		{
			return $this->getCurrentPageIndex() == ($this->getPageCount()-1);
		}

		/**
		 * Returns the total number of pages.
		 * @documentable
		 * @return integer Returns the total number of pages.
		 */
		public function getPageCount()
		{
			return $this->_pageCount;
		}
	}

?>