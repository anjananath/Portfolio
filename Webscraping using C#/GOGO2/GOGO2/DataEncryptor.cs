using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Security.Cryptography;
using System.IO;

namespace GOGO2
{
    public class DataEncryptor
    {
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
    }
}
