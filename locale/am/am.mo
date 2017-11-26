"<</cupsInteger0 258/PageSize[612 936]/ImagingBBox null>>setpagedevice"
  CustomMedia "Legal/Legal 8.5x14in" 612 1008 18.00 14.40 18.00 14.40 "<</cupsInteger0 5/PageSize[612 1008]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 5/PageSize[612 1008]/ImagingBBox null>>setpagedevice"

// Envelope
  CustomMedia "EnvA2/A2 Envelope 4.37x5.75in" 314.64 414 19.08 14 19.08 14 "<</cupsInteger0 96/PageSize[314.64 414]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[314.64 414]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvC6/C6 Envelope 114x162mm" 323.28 459.36 19.56 14.40 19.56 14.40 "<</cupsInteger0 96/PageSize[323.28 459.36]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[323.28 459.36]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvChou4/#4 Japanese Envelope 90x205mm" 254.88 581.04 19.92 14 19.92 14 "<</cupsInteger0 96/PageSize[254.88 581.04]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[254.88 581.04]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvMonarch/Monarch Envelope 3.875x7.5in" 279 540 18.00 14.40 18.00 14.40 "<</cupsInteger0 96/PageSize[279 540]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[279 540]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvDL/DL Envelope 110x220mm" 311.76 623.52 18.00 14.40 18.00 14.40 "<</cupsInteger0 27/PageSize[311.76 623.52]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 27/PageSize[311.76 623.52]/ImagingBBox null>>setpagedevice"
  CustomMedia "Env10/#10 Envelope 4.12x9.5in" 297 684 18.00 14.40 18.00 14.40 "<</cupsInteger0 20/PageSize[297 684]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 20/PageSize[297 684]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvChou3/#3 Japanese Envelope 120x235mm" 339.84 666 18 14 18 14 "<</cupsInteger0 96/PageSize[339.84 666]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[339.84 666]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvC5/C5 Envelope 162x229mm" 459 649 18.3 14.40 18.3 14.40 "<</cupsInteger0 96/PageSize[459 649]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[459 649]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvB5/B5 Envelope 176x250mm" 498.96 708.48 19.08 14 19.08 14 "<</cupsInteger0 96/PageSize[498.96 708.48]/ImagingBBox null>>setpagedevice"
         "<</cupsInteger0 96/PageSize[498.96 708.48]/ImagingBBox null>>setpagedevice"

    // Custom page sizes from 1x4in to Legal
    HWMargins 18 14 18 14
    VariablePaperSize Yes
    MinSize 1in 4in
    MaxSize 8.5in 14in

  {
    Option "Duplex/Double-Sided Printing" PickOne AnySetup 10.0
      Choice "DuplexNoTumble/Long Edge (Standard)" "<</Duplex true/Tumble false>>setpagedevice"
      Choice "DuplexTumble/Short Edge (Flip)" "<</Duplex true/Tumble true>>setpagedevice"
      *Choice "None/Off" "<</Duplex false/Tumble false>>setpagedevice"


    // MediaPosition values map to MediaSource enumeration in global_types.h
    Option "InputSlot/Media Source" PickOne AnySetup 10.0
      *Choice "Auto/Auto-Select" "<</MediaPosition 7>>setpagedevice"
      Choice "PhotoTray/Photo Tray" "<</MediaPosition 1>>setpagedevice"
      Choice "Upper/Upper Tray" "<</MediaPosition 1>>setpagedevice"
      Choice "Lower/Lower Tray" "<</MediaPosition 4>>setpagedevice"
      Choice "Envelope/Envelope Feeder" "<</MediaPosition 3>>setpagedevice"
      Choice "LargeCapacity/Large Capacity Tray" "<</MediaPosition 5>>setpagedevice"
      Choice "Manual/Manual Feeder" "<</MediaPosition 2>>setpagedevice"
      Choice "MPTray/Multi Purpose Tray" "<</MediaPosition 1>>setpagedevice"

    // Duplexer is optional...
    Installable "OptionDuplex/Duplexer Installed"

    // Constraints
    //UIConstraints "*Duplex *OptionDuplex False"

    // <%LJZjsColor:Normal%>
    {
      ModelName "HP Color LaserJet cp1215"
      Attribute "NickName" "" "HP Color LaserJet cp1215, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP Color LaserJet cp1215"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp color laserjet cp1215;DES:hp color laserjet cp1215;"
      PCFileName "hp-color_laserjet_cp1215.ppd"
      Attribute "Product" "" "(HP Color LaserJet cp1215 Printer)"
    }
    {
      ModelName "HP Color LaserJet cp1217"
      Attribute "NickName" "" "HP Color LaserJet cp1217, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP Color LaserJet cp1217"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp color laserjet cp1217;DES:hp color laserjet cp1217;"
      PCFileName "hp-color_laserjet_cp1217.ppd"
      Attribute "Product" "" "(HP Color LaserJet cp1217 Printer)"
    }
    {
      ModelName "HP Color LaserJet 1600"
      Attribute "NickName" "" "HP Color LaserJet 1600, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP Color LaserJet 1600"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp color laserjet 1600;DES:hp color laserjet 1600;"
      PCFileName "hp-color_laserjet_1600.ppd"
      Attribute "Product" "" "(HP Color LaserJet 1600 Printer)"
    }
    {
      ModelName "HP Color LaserJet 2600n"
      Attribute "NickName" "" "HP Color LaserJet 2600n, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP Color LaserJet 2600n"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp color laserjet 2600n;DES:hp color laserjet 2600n;"
      PCFileName "hp-color_laserjet_2600n.ppd"
      Attribute "Product" "" "(HP Color LaserJet 2600n Printer)"
    }
  } // End Supported media sizes.

  {
    Option "InputSlot/Media Source" PickOne AnySetup 10.0
      *Choice "Auto/Auto-Select" "<</MediaPosition 7>>setpagedevice"
      Choice "Manual/Manual Feeder" "<</MediaPosition 2>>setpagedevice"

    Attribute "hpLJZjsColorVersion" "" "2"
    // <%LJZjsColor:Advanced%>
    {
      ModelName "HP LaserJet cp1025nw"
      Attribute "NickName" "" "HP LaserJet cp1025nw, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP LaserJet cp1025nw"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp laserjet cp1025nw;DES:hp laserjet cp1025nw;"
      PCFileName "hp-laserjet_cp1025nw.ppd"
      Attribute "Product" "" "(HP LaserJet Pro cp1025nw Color Printer Series)"
    }
    {
      ModelName "HP LaserJet cp1025"
      Attribute "NickName" "" "HP LaserJet cp1025, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP LaserJet cp1025"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp laserjet cp1025;DES:hp laserjet cp1025;"
      PCFileName "hp-laserjet_cp1025.ppd"
      Attribute "Product" "" "(HP LaserJet Pro cp1025 Color Printer Series)"
    }
    {
      ModelName "HP LaserJet Cp 1025nw"
      Attribute "NickName" "" "HP LaserJet Cp 1025nw, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP LaserJet Cp 1025nw"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp laserjet cp 1025nw;DES:hp laserjet cp 1025nw;"
      PCFileName "hp-laserjet_cp_1025nw.ppd"
      Attribute "Product" "" "(HP LaserJet Pro Cp 1025nw Color Printer Series)"
    }
    {
      ModelName "HP LaserJet Cp 1025"
      Attribute "NickName" "" "HP LaserJet Cp 1025, hpcups $Version, requires proprietary plugin"
      Attribute "ShortNickName" "" "HP LaserJet Cp 1025"
      Attribute "1284DeviceID" "" "MFG:Hewlett-Packard;MDL:hp laserjet cp 1025;DES:hp laserjet cp 1025;"
      PCFileName "hp-laserjet_cp_1025.ppd"
      Attribute "Product" "" "(HP LaserJet Pro Cp 1025 Color Printer Series)"
    }
  }
} // End LJZjsColor (for proprietary plugin)

///////////////////// Hbpl1 (for proprietary plugin)
{
  Attribute "hpPrinterLanguage" "" "hbpl1"

  Group "General/General"

  // cupsMediaType values map to MEDIATYPE from global_types.h
  Option "MediaType/Media Type" PickOne AnySetup 10.0
    Choice "Auto/Unspecified" "<</MediaType(auto)>>setpagedevice"
    *Choice "Plain/Plain Paper" "<</MediaType(Stationery)>>setpagedevice"
    Choice "HPEcoSMARTLite/HP EcoSMART Lite" "<</MediaType(HPEcoSMARTLite)>>setpagedevice"
    Choice "Light/Light 60-74g" "<</MediaType(light)>>setpagedevice"
    Choice "Mid-Weight/Mid-Weight96-110g" "<</MediaType(midweight)>>setpagedevice"
    Choice "Heavy/Heavy 111-130g" "<</MediaType(heavy)>>setpagedevice"
    Choice "ExtraHeavy/Extra Heavy 131-175g" "<</MediaType(extraHeavy)>>setpagedevice"
    Choice "Transparency/Monochrome Laser Transparency" "<</MediaType(transparencyMonoLaser)>>setpagedevice"
    Choice "Labels/Labels" "<</MediaType(labels)>>setpagedevice"
    Choice "Letterhead/Letterhead" "<</MediaType(stationery-letterhead)>>setpagedevice"
    Choice "Envelope/Envelope" "<</MediaType(envelope)>>setpagedevice"
    Choice "Preprinted/Preprinted" "<</MediaType(stationery-preprinted)>>setpagedevice"
    Choice "Prepunched/Prepunched" "<</MediaType(stationery-prepunched)>>setpagedevice"
    Choice "Colored/Colored" "<</MediaType(color)>>setpagedevice"
    Choice "Bond/Bond" "<</MediaType(bond)>>setpagedevice"
    Choice "Recycled/Recycled" "<</MediaType(recycled)>>setpagedevice"
    Choice "Rough/Rough" "<</MediaType(rough)>>setpagedevice"


  //MediaPosition
  Option "InputSlot/Media Source" PickOne AnySetup 10.0
    Choice "Manual/Manual Feeder" "<</MediaPosition 12>>setpagedevice"
    *Choice "tray1/Tray1" "<</MediaPosition 19>>setpagedevice"

   //Economode
    Option "EconoMode/EconoMode" Boolean AnySetup 10.0
    *Choice "False/Off" "<</cupsInteger2 0>>setpagedevice"
     Choice "True/On" "<</cupsInteger2 1>>setpagedevice"


// 4x6 or smaller
  CustomMedia "A6/A6 105x148mm" 297.36 419.76 14 14 14 14 "<</cupsString0(iso_a6_105x148mm)/PageSize[298 420]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(iso_a6_105x148mm)/PageSize[297.36 419.76]/ImagingBBox null>>setpagedevice"

// 5x7
  CustomMedia "A5/A5 148x210mm" 419.76 595.44 14 14 14 14 "<</cupsString0(iso_a5_148x210mm)/PageSize[420 595]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(iso_a5_148x210mm)/PageSize[419.76 595.44]/ImagingBBox null>>setpagedevice"

// Standard
  CustomMedia "B5/JB5 182x257mm" 516.24 728.64 14 14 14 14 "<</cupsString0(jis_b5_182x257mm)/PageSize[516 729]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(jis_b5_182x257mm)/PageSize [516.24 728.64]/ImagingBBox null>>setpagedevice"
  CustomMedia "Executive/Executive 7.25x10.5in" 522 756 14 14 14 14 "<</cupsString0(na_executive_7.25x10.5in)/PageSize[522 756]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_executive_7.25x10.5in)/PageSize[522 756]/ImagingBBox null>>setpagedevice"
  CustomMedia "195x270mm/16k 195x270mm" 552 765 14 14 14 14 "<</cupsString0(prc_16k_195x270mm)/PageSize[553 765]/ImagingBBox null>>setpagedevice"
         "<</cupsString0 (prc_16k_195x270mm)/PageSize[552 765]/ImagingBBox null>>setpagedevice"
  CustomMedia "184x260mm/16k 184x260mm" 525 720 14 14 14 14 "<</cupsString0(prc_16k_184x260mm)/PageSize[522 737]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(prc_16k_184x260mm)/PageSize[525 720]/ImagingBBox null>>setpagedevice"
  CustomMedia "Envroc16k/16k 197x273mm" 554 774 14 14 14 14 "<</cupsString0(prc_16k_197x273mm)/PageSize[558 774]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(prc_16k_197x273mm)/PageSize[554 774]/ImagingBBox null>>setpagedevice"
  *CustomMedia "Letter/Letter 8.5x11in" 612 792 14 14 14 14 "<</cupsString0(na_letter_8.5x11in)/PageSize[612 792]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_letter_8.5x11in)/PageSize[612 792]/ImagingBBox null>>setpagedevice"
  CustomMedia "A4/A4 210x297mm" 595.44 841.68 14 14 14 14 "<</cupsString0(iso_a4_210x297mm)/PageSize[595 842]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(iso_a4_210x297mm)/PageSize[595.44 841.68]/ImagingBBox null>>setpagedevice"
  CustomMedia "Legal/Legal 8.5x14in" 612 1008 14 14 14 14 "<</cupsString0(na_legal_8.5x14in)/PageSize[612 1008]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_legal_8.5x14in)/PageSize[612 1008]/ImagingBBox null>>setpagedevice"
  CustomMedia "8.5x13in/Oficio 8.5x13" 612 936 14 14 14 14 "<</cupsString0(na_foolscap_8.5x13in)/PageSize[612 936]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_foolscap_8.5x13in)/PageSize[612 936]/ImagingBBox null>>setpagedevice"
  CustomMedia "216x340mm/Oficio 216x340mm" 612 936 14 14 14 14 "<</cupsString0(na_legal_216x340mm)/PageSize[612 964]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_legal_216x340mm)/PageSize[612 964]/ImagingBBox null>>setpagedevice"
  CustomMedia "Postcard/Postcard (JIS)" 283 420 14 14 14 14 "<</cupsString0(jpn_hagaki_100x148mm)/PageSize[283 420]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(jpn_hagaki_100x148mm)/PageSize[283 420]/ImagingBBox null>>setpagedevice"
  CustomMedia "DoublePostcardRotated/Double Postcard (JIS)" 420 567 14 14 14 14 "<</cupsString0(jpn_oufuku_148x200mm)/PageSize[420 567]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(jpn_oufuku_148x200mm)/PageSize[420 567]/ImagingBBox null>>setpagedevice"

// Envelope
  CustomMedia "EnvMonarch/Monarch Envelope 3.875x7.5in" 279 540 14 14 14 14 "<</cupsString0(na_monarch_3.875x7.5in)/PageSize[279 540]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_monarch_3.875x7.5in)/PageSize[279 540]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvDL/DL Envelope 110x220mm" 312 624 14 14 14 14 "<</cupsString0(iso_dl_110x220mm)/PageSize[312 624]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(iso_dl_110x220mm)/PageSize[312 624]/ImagingBBox null>>setpagedevice"
  CustomMedia "Env10/#10 Envelope 4.12x9.5in" 297 684  14 14 14 14 "<</cupsString0(na_number-10_4.125x9.5in)/PageSize[297 684]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(na_number-10_4.125x9.5in)/PageSize[297 684]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvC5/C5 Envelope 162x229mm" 459 649 14 14 14 14 "<</cupsString0(iso_c5_162x229mm)/PageSize[459 649]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(iso_c5_162x229mm)/PageSize[459 649]/ImagingBBox null>>setpagedevice"
  CustomMedia "EnvISOB5/B5 Envelope 176x250mm" 499 709 14 14 14 14 "<</cupsString0(iso_b5_176x250mm)/PageSize[499 709]/ImagingBBox null>>setpagedevice"
         "<</cupsString0(iso_b5_176x250mm)/PageSize[499 709]/ImagingBBox null>>setpagedevice"

    // Custom page sizes from 1x4in to Legal
    HWMargins 14 14 14 14
    VariablePaperSize Yes
    MinSize 3in 5in
    MaxSize 8.5in 14in

  {

   //Constraints

   // cupsRowCount values map to PEN_TYPE + 1 from global_types.h
    Option "ColorModel/Output Mode" PickOne AnySetup 10.0
    *Choice "Gray/Grayscale" "<</cupsColorSpace 1/cupsBitsPerColor 8/cupsRowCount 3/cupsRowStep 2>>setpagedevice"

   // cupsMediaType values map to MEDIATYPE from global_types.h
     Option "MediaType/Media Type" PickOne AnySetup 10.0
     Choice "Vellum/Vellum" "<</MediaType(vellum)>>setpagedevice"

    //cupsCompression values map to QUALITY_MODE from global_types.h
    Option "OutputMode/Print Quality" PickOne AnySetup 10.0
    *Choice "FastRes600/FastRes 600" "<</OutputType(normal)/HWResolution[600 600]>>setpagedevice"
    Choice "FastRes1200/FastRes 1200" "<</OutputType(best)/HWResolution[600 600]>>setpagedevice"

   // <%Hbpl1:Mono%>
   {
     ModelName "HP LaserJet m101-m106"
     Attribute "NickName" "" "HP LaserJet m101-m106, hpcups $Version"
     Att