using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using HtmlAgilityPack;
using System.Data;
using System.IO;
using GOGO2.RightnowServiceReference;
namespace GOGO2
{
    class Program
    {
        static void Main(string[] args)
        {
            Console.BufferHeight = 32766;

            string filePath = @"C:\Users\anjana.nath\Desktop\BatchErrors_" + DateTime.Now.ToString("yyyyMMddhhmmsstt") + ".txt";
            var errorfile = File.Create(filePath);
            errorfile.Close();

            string goodfile = @"C:\Users\anjana.nath\Desktop\good_" + DateTime.Now.ToString("yyyyMMddhhmmsstt") + ".txt";

            string badfile = @"C:\Users\anjana.nath\Desktop\bad_" + DateTime.Now.ToString("yyyyMMddhhmmsstt") + ".txt";

            var goodfilecreate = File.Create(goodfile);

            goodfilecreate.Close();

            var badfilecreate = File.Create(badfile);

            badfilecreate.Close();

            HtmlDocument htmldoc = new HtmlDocument();

            htmldoc.Load(@"C:\Users\anjana.nath\Desktop\Gogo New Tasks\Video.htm");

            DataTable datatable = new DataTable();

            var htmlnode = htmldoc.DocumentNode.SelectNodes("//table/tr");

            var theads = htmlnode[0].Elements("th").Select(th => th.InnerText.Trim());

            foreach (var head in theads)
            {
                datatable.Columns.Add(head);
            }

            datatable.Columns.Add("Checksum",typeof(string));

            var rows = htmlnode.Skip(1).Select(tr => tr.Elements("td").Select(td => td.InnerText.Trim()).ToArray());

            foreach (var row in rows)
             {
                var sum = "";
              
               foreach (var r in row)
                {
                  sum = sum + r;
                    
             }
               // DataEncryptor keys = new DataEncryptor();

                string encr = MD5(sum);

                List<string> list = new List<string>(row);

                list.Add(encr); // Adding checksum value to data table last column

                datatable.Rows.Add(list.ToArray()); // adding the row to data table
            }

            
            //CSV Creation 
          /* string strFilePath = "C:/Users/anjana.nath/Desktop/Datatable.csv";
            try
            {
                StreamWriter sw = new StreamWriter(strFilePath, false);
                int columnCount = datatable.Columns.Count;

                for (int i = 0; i < columnCount; i++)
                {
                    sw.Write(datatable.Columns[i]);

                    if (i < columnCount - 1)
                    {
                        sw.Write(",");
                    }
                }

                sw.Write(sw.NewLine);

                foreach (DataRow dr in datatable.Rows)
                {
                    for (int i = 0; i < columnCount; i++)
                    {
                        if (!Convert.IsDBNull(dr[i]))
                        {
                            sw.Write(dr[i].ToString());
                        }

                        if (i < columnCount - 1)
                        {
                            sw.Write(",");
                        }
                    }

                    sw.Write(sw.NewLine);
                }
                Console.WriteLine("CSV Created");
                sw.Close();
            }
            catch (Exception ex)
            {
                throw ex;
            }*/
           

            int full_batch = (datatable.Rows.Count) / 100;
            
            int rem_batch = (datatable.Rows.Count) % 100;
            int k = 0;
            if (full_batch > 0)
            {
                
                for (int i = 1; i <= full_batch; i++)
                {
                    int count = 0;
                    Console.WriteLine("Batch " + i);
                    BatchRequestItem[] requestItems = new BatchRequestItem[100];
                    for (int j = 0; j < 3; j++)
                    {
                       
                        BatchRequestItem item = new BatchRequestItem();
                       
                        item = createVideoAvailability(datatable.Rows[k]);
                      
                        if (item != null)
                        {
                           
                           Console.WriteLine("Row : "+k+"\n");
                            requestItems[count] = item;
                            requestItems[count].CommitAfter = true;
                            requestItems[count].CommitAfterSpecified = true;
                            count++;
                           

                        }
                        else
                        {
                           //Console.WriteLine("Error in the record with tail: " + datatable.Rows[k][0]);
                            System.IO.File.AppendAllText(filePath, Environment.NewLine + "Error in the record with tail: " + datatable.Rows[k][0]);
                        }
                        k++;
                        
                    }

                   submitBatch(requestItems, goodfile, badfile);
                   
                   
                }
               
               
            }
            if (rem_batch > 0)
            {


                BatchRequestItem[] requestItems = new BatchRequestItem[rem_batch];
                int c = 0;
                for (int j = 0; j < rem_batch; j++)
                {
                    Console.WriteLine("Remaining Batch " + j);
                    BatchRequestItem item = new BatchRequestItem();
                    item = createVideoAvailability(datatable.Rows[k]);
                    if (item != null)
                    {
                        Console.WriteLine("Row : " + k + "\n");
                        requestItems[c] = item;
                        requestItems[c].CommitAfter = true;
                        requestItems[c].CommitAfterSpecified = true;
                        c++;

                    }
                    else
                    {
                        System.IO.File.AppendAllText(filePath, Environment.NewLine + datatable.Rows[k][0] + " -Error in input record");
                    }
                    k++;

                }


                submitBatch(requestItems, goodfile, badfile);

            }
            Console.WriteLine("Batch submit Success. Please chekc the good and bad files to review the responses.");
            Console.ReadLine();
        }

        public static string MD5(string row_string)
        {
            byte[] textBytes = System.Text.Encoding.Default.GetBytes(row_string);
            try
            {
                System.Security.Cryptography.MD5CryptoServiceProvider cryptHandler;
                cryptHandler = new System.Security.Cryptography.MD5CryptoServiceProvider();
                byte[] hash = cryptHandler.ComputeHash(textBytes);
                string ret = "";
                foreach (byte a in hash)
                {
                    //if (a < 16)
                    //    ret += "0" + a.ToString("x");
                    //else
                    //    ret += a.ToString("x");
                    ret += a.ToString("x2");
                }
                return ret;
            }
            catch
            {
                throw;
            }
        }

        public static CreateMsg InsertTailInfo(DataRow row)
        {
            BatchRequestItem createItem = new BatchRequestItem();
            RightNowSyncPortClient _client = new RightNowSyncPortClient();
            _client.ClientCredentials.UserName.UserName = "donotdelete";
            _client.ClientCredentials.UserName.Password = "Donotdelete@123";

            GenericObject go = new GenericObject();

            //Set the object type
            RNObjectType objType = new RNObjectType();
            objType.Namespace = "CO";
            objType.TypeName = "TailInfo_v2";
            go.ObjectType = objType;

            List<GenericField> gfs = new List<GenericField>();

            gfs.Add(createGenericField("Tail", ItemsChoiceType.StringValue, row[0].ToString()));
            gfs.Add(createGenericField("ACPU", ItemsChoiceType.StringValue, row[5].ToString()));
            gfs.Add(createGenericField("airline", ItemsChoiceType.StringValue, row[1].ToString()));

           go.GenericFields = gfs.ToArray();
            //ClientInfoHeader clientInfoHeader = new ClientInfoHeader();
            //clientInfoHeader.AppID = "Insert into Tail Info";
            //CreateProcessingOptions cpo = new CreateProcessingOptions();
            //cpo.SuppressExternalEvents = false;
            //cpo.SuppressRules = false;

            //RNObject[] results = _client.Create(clientInfoHeader, new RNObject[] { go }, cpo);
            RNObject[] newObjects = new RNObject[] { go };

            CreateMsg createMsg = new CreateMsg();

            CreateProcessingOptions options = new CreateProcessingOptions();
            options.SuppressExternalEvents = false;
            options.SuppressRules = false;
            createMsg.ProcessingOptions = options;
            createMsg.RNObjects = newObjects;
           // createItem.Item = createMsg;
            return createMsg;

        }

        private static BatchRequestItem createVideoAvailability(DataRow row)
        {
          
            BatchRequestItem createItem = new BatchRequestItem();
          
            try
            {
                string checksum = "0";
                RightNowSyncPortClient _client = new RightNowSyncPortClient();
                _client.ClientCredentials.UserName.UserName = "donotdelete";
                _client.ClientCredentials.UserName.Password = "Donotdelete@123";

                ClientInfoHeader clientInfoHeader = new ClientInfoHeader();
                clientInfoHeader.AppID = "Custom object updation";

                
                String queryString = "SELECT CO.Video_Availability FROM CO.Video_Availability  WHERE CO.Video_Availability.Tail = '" + row[0].ToString()+"'";
               
                GenericObject video = new GenericObject();

                //Set the object type
                RNObjectType objType = new RNObjectType();
                objType.Namespace = "CO";
                objType.TypeName = "Video_Availability";
                video.ObjectType = objType;

                RNObject[] objTemplates = new RNObject[] { video };
                
                //byte[] byteArray;
                //CSVTableSet queryCSV = _client.QueryCSV(clientInfoHeader, queryString, 10000, ",", false, true, out byteArray);
                //CSVTable[] csvTables = queryCSV.CSVTables;

                QueryResultData[] queryObj = _client.QueryObjects(clientInfoHeader, queryString, objTemplates, 10000);

                RNObject[] rnObj = queryObj[0].RNObjectsResult;

                video = null;

                 GenericObject newrsvporg = new GenericObject();

                //Set the object type
                RNObjectType objtype = new RNObjectType();
                objtype.Namespace = "CO";
                objtype.TypeName = "Video_Availability";
                newrsvporg.ObjectType = objtype;

                List<GenericField> gfs = new List<GenericField>();
                
               
                if (row[0].ToString() != "")
                {

                    gfs.Add(createGenericField("Tail", ItemsChoiceType.StringValue, row[0].ToString()));
                }
                if (row[1].ToString() != "")
                {

                    gfs.Add(createGenericField("Airline", ItemsChoiceType.StringValue, row[1].ToString()));

                }
                

                if (row[2].ToString() != "")
                {

                    gfs.Add(createGenericField("IP_Subnet", ItemsChoiceType.StringValue, row[2].ToString()));

                }
                

                if (row[3].ToString() != "")
                {

                    gfs.Add(createGenericField("AC_Type", ItemsChoiceType.StringValue, row[3].ToString()));

                }
                

                if (row[4].ToString() != "")
                {

                    gfs.Add(createGenericField("Tech", ItemsChoiceType.StringValue, row[4].ToString()));

                }
                

                if (row[5].ToString() != "")
                {

                    gfs.Add(createGenericField("ACPU", ItemsChoiceType.StringValue, row[5].ToString()));
                }
                
                if (row[6].ToString() != "")
                {

                    gfs.Add(createGenericField("Plus_Bundle", ItemsChoiceType.StringValue, row[6].ToString()));
                }
                             

                if (row[7].ToString() != "")
                {

                    gfs.Add(createGenericField("Video_Plugin", ItemsChoiceType.StringValue, row[7].ToString()));
                }
                

                if (row[8].ToString() != "")
                {

                    gfs.Add(createGenericField("Asp_Enabled", ItemsChoiceType.StringValue, row[8].ToString()));
                }
                

                if (row[9].ToString() != "")
                {

                    gfs.Add(createGenericField("Catalog", ItemsChoiceType.StringValue, row[9].ToString()));
                }
                
                
                if (row[10].ToString() != "")
                {

                    gfs.Add(createGenericField("Storefront_Build", ItemsChoiceType.StringValue, row[10].ToString()));

                }
                
               
                if (row[11].ToString() != "")
                {

                    gfs.Add(createGenericField("ACPU_SN", ItemsChoiceType.StringValue, row[11].ToString()));

                }



                if (row[12].ToString() != "")
                {

                    StringBuilder sb = new StringBuilder();
                    string inputString = row[12].ToString();

                    //string inputString = "Hello\b1df";

                    String input = inputString.Replace("\b", "");




                    gfs.Add(createGenericField("AACU_SN", ItemsChoiceType.StringValue, input));


                }
                
                

                if (row[13].ToString() != "")
                {
                    gfs.Add(createGenericField("CL_Support", ItemsChoiceType.StringValue, row[13].ToString()));
                }
                
                
                if (row[14].ToString() != "")
                {
                    gfs.Add(createGenericField("Batch", ItemsChoiceType.StringValue, row[14].ToString()));
                }
                
               
                if (row[15].ToString() != "")
                {

                    gfs.Add(createGenericField("USB1", ItemsChoiceType.StringValue, row[15].ToString()));
                }
               
                
                if (row[16].ToString() != "")
                {

                    gfs.Add(createGenericField("USB2", ItemsChoiceType.StringValue, row[16].ToString()));
                }
                
               
                if (row[17].ToString() != "")
                {

                    gfs.Add(createGenericField("Primary_HDD", ItemsChoiceType.StringValue, row[17].ToString()));
                }
                
                if (row[18].ToString() != "")
                {

                    gfs.Add(createGenericField("Other_HDD", ItemsChoiceType.StringValue, row[18].ToString()));
                }
                
               
                if (row[19].ToString() != "")
                {

                    gfs.Add(createGenericField("Media_Count", ItemsChoiceType.StringValue, row[19].ToString()));
                }
                
                
                if (row[20].ToString() != "")
                {

                    gfs.Add(createGenericField("WV_Titles_Percentage", ItemsChoiceType.StringValue, row[20].ToString()));
                }
                
               
                if (row[21].ToString() != "")
                {

                    gfs.Add(createGenericField("Adobe_Tarball_Percentage", ItemsChoiceType.StringValue, row[21].ToString()));
                }
                
                
                if (row[22].ToString() != "")
                {

                    gfs.Add(createGenericField("Adobe_Titles_Percentage", ItemsChoiceType.StringValue, row[22].ToString()));
                }
                
               
                if (row[23].ToString() != "")
                {

                    gfs.Add(createGenericField("AAA", ItemsChoiceType.StringValue, row[23].ToString()));
                }
                
              
                if (row[24].ToString() != "")
                {

                    gfs.Add(createGenericField("Last_IFE_Purchase", ItemsChoiceType.StringValue, row[24].ToString()));
                }
                
               
                if (row[25].ToString() != "")
                {

                    gfs.Add(createGenericField("Last_IFE_View", ItemsChoiceType.StringValue, row[25].ToString()));
                }
                
                
                if (row[26].ToString() != "")
                {

                    gfs.Add(createGenericField("Last_Flight", ItemsChoiceType.StringValue, row[26].ToString()));
                }
                
                
                if (row[27].ToString() != "")
                {

                    gfs.Add(createGenericField("Checksum", ItemsChoiceType.StringValue, row[27].ToString()));
                }
                

                newrsvporg.GenericFields = gfs.ToArray();
                
                // foreach (CSVTable table in csvTables)
                //{
                //    String[] rowData = table.Rows;
                    
                //    foreach (String data in rowData)
                //    {
                //        checksum = data;
                        
                //    }

                //}

                foreach (RNObject obj in rnObj)
                {

                  video = (GenericObject)obj;
                  GenericField[] gnf = video.GenericFields;
                   foreach (GenericField field in gnf)
                   {
                      
                      if (field.name == "Checksum")
                      {
                          checksum = field.DataValue.Items[0].ToString();
                          
                      }
                   }
                }
                
                 if (rnObj.Count()== 0)
                {
                  //  CreateMsg tailmsg = InsertTailInfo(row);
                    RNObject[] newObjects = new RNObject[] { newrsvporg };

                    CreateMsg createMsg = new CreateMsg();

                    CreateProcessingOptions options = new CreateProcessingOptions();
                    options.SuppressExternalEvents = false;
                    options.SuppressRules = false;
                    createMsg.ProcessingOptions = options;
                    createMsg.RNObjects = newObjects;
                    //createItem.Item = tailmsg;
                    createItem.Item = createMsg;
                    return createItem;
                }
                else
                {
                   if(checksum != (row[27].ToString()))
                    {
                      
                    newrsvporg.ID = new ID();
                    newrsvporg.ID.id = video.ID.id;
                    newrsvporg.ID.idSpecified = true;

                    RNObject[] newObjects = new RNObject[] { newrsvporg };
                    UpdateMsg updateMsg = new UpdateMsg();

                    UpdateProcessingOptions options = new UpdateProcessingOptions();
                    options.SuppressExternalEvents = false;
                    options.SuppressRules = false;
                    updateMsg.ProcessingOptions = options;
                    updateMsg.RNObjects = newObjects;
                    createItem.Item = updateMsg;
                    return createItem;
                    }
                }
                 
            }
            catch (Exception ex)
            {
                
                createItem = null;

               
            }
           
            return null;
        }

        private static GenericField createGenericField(string Name, ItemsChoiceType itemsChoiceType, object Value)
        {
            GenericField gf = new GenericField();
            gf.name = Name;
            gf.DataValue = new DataValue();
            gf.DataValue.ItemsElementName = new ItemsChoiceType[] { itemsChoiceType };
            //if (Value == "")
            //{
            //    Value = " ";
            //}
            gf.DataValue.Items = new object[] { Value };
            
            //gf.DataValue.Items = new object[] { Value };
            return gf;
        }

        

        private static void submitBatch(BatchRequestItem[] requestItems, string opfilepath, string erfilepath)
        {
            string str = ""; 
            string errorstring = "";
            
            RightNowSyncPortClient _client = new RightNowSyncPortClient();
            _client.ClientCredentials.UserName.UserName = "donotdelete";
            _client.ClientCredentials.UserName.Password = "Donotdelete@123";
            ClientInfoHeader clientInfoHeader = new ClientInfoHeader();
            clientInfoHeader.AppID = "Batcher";
            try
            {
                BatchResponseItem[] batchRes = _client.Batch(clientInfoHeader, requestItems);
                for (int i = 0; i < batchRes.Length; i++)
                {
                    
                    if (batchRes[i].ItemElementName == ItemChoiceType.CreateResponseMsg)
                    {
                        
                        string tail=" ";
                        string airline=" ";
                        string acpu = " ";
                        long tailid = 0;
                        CreateResponseMsg createResponseMsg = (CreateResponseMsg)batchRes[i].Item;
                        RNObject[] createdObjects = createResponseMsg.RNObjectsResult;
                        
                        
                        foreach (RNObject obj in createdObjects)
                        {
                            
                            var createmsg = (CreateMsg)requestItems[i].Item;

                            GenericObject fetchedInc = (GenericObject)createmsg.RNObjects[0];
                            GenericField[] genericFields = fetchedInc.GenericFields;

                            foreach (GenericField field in genericFields)
                            {
                                if (String.Compare(field.name,"Tail", true) == 0)
                                {
                                    tail = (String)field.DataValue.Items[0];
                                  //  Console.WriteLine((String)field.DataValue.Items[0]);
                                }
                                if (String.Compare(field.name,"Airline", true) == 0)
                                {
                                    airline = (String)field.DataValue.Items[0];
                                  //  Console.WriteLine((String)field.DataValue.Items[0]);
                                }
                                if (String.Compare(field.name,"ACPU", true) == 0)
                                {
                                    acpu = (String)field.DataValue.Items[0];
                                   // Console.WriteLine((String)field.DataValue.Items[0]);
                                }
                            }
                            GenericObject go = new GenericObject();
                            RNObjectType objType = new RNObjectType();
                            objType.Namespace = "CO";
                            objType.TypeName = "TailInfo_v2";
                            go.ObjectType = objType;

                            List<GenericField> gfs = new List<GenericField>();

                            gfs.Add(createGenericField("Tail", ItemsChoiceType.StringValue, tail));
                            gfs.Add(createGenericField("airline", ItemsChoiceType.StringValue, airline));
                            gfs.Add(createGenericField("ACPU", ItemsChoiceType.StringValue, acpu));


                            go.GenericFields = gfs.ToArray();


                            CreateProcessingOptions cpo = new CreateProcessingOptions();
                            cpo.SuppressExternalEvents = false;
                            cpo.SuppressRules = false;
                            RNObject[] results = _client.Create(clientInfoHeader, new RNObject[] { go }, cpo);
                            // Console.WriteLine(results.ToString());
                            // Console.ReadLine();
                            if (results != null && results.Length > 0)
                            {
                                foreach (GenericObject result in results)
                                {
                                    if (result != null && result.ID != null)
                                    {
                                       // Console.WriteLine("*** Created " + result.GetType().ToString() + " with ID " + result.ID.id);
                                        tailid = result.ID.id;
                                    }
                                }
                            }
                           // string retrun_string = updateVideoObject(tail,tailid);
                            
                           
                            var data = (GOGO2.RightnowServiceReference.GenericObject)createmsg.RNObjects[0];
                            str = str + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Created";

                           /* GenericField[] attr_list = data.GenericFields;
                            foreach (GenericField field in attr_list)
                            {

                                if (field.name == "Tail")
                                {
                                   
                                    tail = field.DataValue.Items[0].ToString();
                                }

                                if (field.name == "Airline")
                                {

                                    airline = field.DataValue.Items[0].ToString();
                                }
                                if (field.name == "ACPU")
                                {

                                    acpu = field.DataValue.Items[0].ToString();
                                }
                            }*/

                            
                        }

                        

                    }
                    else if (batchRes[i].ItemElementName == ItemChoiceType.UpdateResponseMsg)
                    {
                        UpdateMsg updtmsg = (UpdateMsg)requestItems[i].Item;
                        var data = (GOGO2.RightnowServiceReference.GenericObject)updtmsg.RNObjects[0];
                        str = str + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Updated";
                    }
                    else
                    {
                        if (requestItems[i].Item is CreateMsg)
                        {
                            var createmsg = (CreateMsg)requestItems[i].Item;
                            var plan = createmsg.RNObjects;

                            var data = (GOGO2.RightnowServiceReference.GenericObject)createmsg.RNObjects[0];

                            if (batchRes[i].ItemElementName == ItemChoiceType.RequestErrorFault)
                            {
                                errorstring = errorstring + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Request Error Fault";

                            }
                            else if (batchRes[i].ItemElementName == ItemChoiceType.ServerErrorFault)
                            {
                                errorstring = errorstring + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Server Error Fault";

                            }
                            else
                            {
                                errorstring = errorstring + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Uncategorized";
                            }
                        }

                        if (requestItems[i].Item is UpdateMsg)
                        {
                            var updatemsg = (UpdateMsg)requestItems[i].Item;
                            var plan = updatemsg.RNObjects;

                            var data = (GOGO2.RightnowServiceReference.GenericObject)updatemsg.RNObjects[0];

                            if (batchRes[i].ItemElementName == ItemChoiceType.RequestErrorFault)
                            {
                                errorstring = errorstring + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Request Error Fault";

                            }
                            else if (batchRes[i].ItemElementName == ItemChoiceType.ServerErrorFault)
                            {
                                errorstring = errorstring + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Server Error Fault";

                            }
                            else
                            {
                                errorstring = errorstring + Environment.NewLine + data.GenericFields[0].DataValue.Items[0].ToString() + ", Uncategorized";
                            }

                        }
                    }
                }
                System.IO.File.AppendAllText(opfilepath, Environment.NewLine + str);
                System.IO.File.AppendAllText(erfilepath, Environment.NewLine + errorstring + Environment.NewLine);
            }
            catch (Exception ex)
            {
            //Console.WriteLine("Batch Submit Exception: "+ex.Message+"\n");
           // Console.ReadLine();
              errorstring = errorstring + Environment.NewLine + ex.Message + Environment.NewLine+"-Exception";
              System.IO.File.AppendAllText(erfilepath, Environment.NewLine + errorstring + Environment.NewLine);

            }
   
        }

        //private static string updateVideoObject(string tail,long tailid)
        //{
        //    RightNowSyncPortClient _client = new RightNowSyncPortClient();
        //    _client.ClientCredentials.UserName.UserName = "donotdelete";
        //    _client.ClientCredentials.UserName.Password = "Donotdelete@123";
        //    ClientInfoHeader clientInfoHeader = new ClientInfoHeader();
        //    clientInfoHeader.AppID = "update Tail id in videoavailability";
        //    String queryString = "SELECT CO.Video_Availability FROM CO.Video_Availability  WHERE CO.Video_Availability.Tail = '" + tail+"'";
               
        //        GenericObject video = new GenericObject();

        //        //Set the object type
        //        RNObjectType objTypee = new RNObjectType();
        //        objTypee.Namespace = "CO";
        //        objTypee.TypeName = "Video_Availability";
        //        video.ObjectType = objTypee;

        //        RNObject[] objTemplates = new RNObject[] { video };
        //         QueryResultData[] queryObj = _client.QueryObjects(clientInfoHeader, queryString, objTemplates, 10000);

        //        RNObject[] rnObj = queryObj[0].RNObjectsResult;
        //        foreach (RNObject obj in rnObj)
        //        {

        //            video = (GenericObject)obj;


        //        }
        //   // Console.WriteLine(video.ID.id);
        //   // Console.ReadLine();
        //    GenericObject go = new GenericObject();

        //    //Set the object type
        //    RNObjectType objType = new RNObjectType();
        //    objType.Namespace = "CO";
        //    objType.TypeName = "Video_Availability";
        //    go.ObjectType = objType;

        //    List<GenericField> gfs = new List<GenericField>();
        //    NamedID tailId = new NamedID();
        //    tailId.ID = new ID();
        //    tailId.ID.id = tailid;
        //    tailId.ID.idSpecified = true;
        //    gfs.Add(createGenericField("TailInfo_v2", ItemsChoiceType.NamedIDValue, tailId));
            

        //   go.GenericFields = gfs.ToArray();
        //   go.ID = new ID();
        //   go.ID.id = video.ID.id;
        //   go.ID.idSpecified = true;
            
        //     UpdateProcessingOptions cpo = new  UpdateProcessingOptions();
        //    cpo.SuppressExternalEvents = false;
        //    cpo.SuppressRules = false;

        //   _client.Update(clientInfoHeader, new RNObject[] { go }, cpo);
            
        //    return "true";
           

        //}

    }
}
