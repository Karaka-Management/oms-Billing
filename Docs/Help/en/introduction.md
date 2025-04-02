# Introduction

The **Billing** module is essential for writing and managing invoices either from a sales or procurement perspective.

## Target Group

The target group for this module is the sales and purchase department.

# Setup

If you want to use parts of the automatic invoice recognition for supplier invoices, the following tools must be installed on the server:

* [pdftotext](https://www.xpdfreader.com/pdftotext-man.html) (extract text from PDFs)
* [pdftoppm](https://www.xpdfreader.com/pdftoppm-man.html) (turn pdf to image if text cannot be read from PDF)
* [tesseract-ocr](https://tesseract-ocr.github.io/tessdoc/Downloads.html) (text recognition from images)

# Features

## Payment Terms

Multiple payment terms to indicate when a invoice becomes due.

## Shipping Terms

Multiple shipping terms to indicate how the goods on the invoice are shipped.

### Billing Types

Different billing types such as:

* Invoice
* Delivery note
* Order confirmation
* Offer
* ... more

# Recommendation

Other modules that work great with this one together are:

* [SalesAnalysis]({/}?id=SalesAnalysis)