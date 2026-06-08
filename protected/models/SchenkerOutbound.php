<?php

class SchenkerOutbound extends CFormModel
{

    //set column field database for datatable orderable
    public $column_order = array('id', 'externOrderKey', 'ccompany', 'susr1', 'carrierName', 'actualShipDate', NULL, 'status', null);

    //set column field database for datatable searchable
    public $column_search = array('id', 'externOrderKey', 'ccompany', 'susr1', 'carrierName', 'actualShipDate', NULL, 'status');

    public $order = array('id' => 'desc'); // default order

    public $data;

    public function _get_datatables_query($count = false)
    {

        if ($count === true) {
            $this->data = Yii::app()->db->createCommand()
                ->select('COUNT(*) AS jml_data')
                ->from('view_schenker_outbound');
        } else {
            $this->data = Yii::app()->db->createCommand()
                ->select('*')
                ->from('view_schenker_outbound');
        }



        $i = 0;
        foreach ($this->column_search as $item) // loop column 
        {
            if ($_POST['search']['value']) // if datatable send POST for search
            {

                $keyword = $_POST['search']['value'];
                // escape % and _ characters
                $keyword = strtr($keyword, array('%' => '\%', '_' => '\_'));

                if ($i === 0) // first loop
                {
                    $this->data->where(['like', $item, '%' . $keyword . '%']);
                } else {
                    $this->data->orWhere(['like', $item, '%' . $keyword . '%']);
                }
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $str = $this->column_order[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'];
            $this->data->order($str);
        } else if (isset($this->order)) {
            $order = $this->order;

            $str = key($order) . ' ' . $order[key($order)];
            $this->data->order($str);
        }
    }

    public function get_datatables()
    {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1)
            $this->data->limit($_POST['length'], $_POST['start']);
        return $this->data->queryAll();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query(true);
        return $this->data->queryRow()['jml_data'];
    }

    public function count_all()
    {
        $all = Yii::app()->db->createCommand()
            ->select(array('count(*) as jumlah'))
            ->from('view_schenker_outbound')
            ->queryRow();
        return $all['jumlah'];
    }
}
