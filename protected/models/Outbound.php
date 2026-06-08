<?php

class Outbound extends CFormModel
{

    //set column field database for datatable orderable
    public $column_order = array('id', 'qty', 'po', 'destination', 'delivery_id', 'transporter', 'oh.date_created', 'scan_time', 'oh.date_updated', 'oh.status', null);

    //set column field database for datatable searchable
    public $column_search = array('id', 'qty', 'po', 'destination', 'delivery_id', 'transporter', 'oh.date_created', 'scan_time', 'oh.date_updated', 'oh.status');

    public $order = array('id' => 'desc'); // default order

    public $data;

    public function _get_datatables_query($count = false)
    {

        if ($count === true) {
            $this->data = Yii::app()->db->createCommand()
                ->select('COUNT(*) AS jml_data')
                ->from('el_outbound_header oh')
                ->leftJoin('el_user u1', 'u1.user_id = oh.created_by')
                ->leftJoin('el_user u2', 'u2.user_id = oh.updated_by');
        } else {
            $this->data = Yii::app()->db->createCommand()
                ->select('oh.id, oh.qty, oh.po, oh.destination, oh.delivery_id, oh.transporter, oh.date_created, oh.scan_time, oh.date_updated, oh.status, oh.created_by, oh.updated_by, u1.first_name AS created_by_nama, u2.first_name AS updated_by_nama')
                ->from('el_outbound_header oh')
                ->leftJoin('el_user u1', 'u1.user_id = oh.created_by')
                ->leftJoin('el_user u2', 'u2.user_id = oh.updated_by');
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
            ->from('el_outbound_header')
            ->queryRow();
        return $all['jumlah'];
    }
}
