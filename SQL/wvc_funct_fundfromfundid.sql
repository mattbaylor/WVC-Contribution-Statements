USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_fundfromFundId]    Script Date: 12/30/2012 11:38:40 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/14/2011
-- Description:	Given a fund id return the fund name
-- =============================================
CREATE FUNCTION [dbo].[wvc_funct_fundfromFundId] 
(
	-- Add the parameters for the function here
	@FundID int
)
RETURNS nvarchar(100)
AS
BEGIN
	-- Declare the return variable here
	DECLARE @ReturnVar nvarchar(100)

	-- Add the T-SQL statements to compute the return value here
	SELECT @ReturnVar = (
		SELECT 
			CASE LEN(cf.online_name)
				WHEN 0 THEN cf.fund_name
				ELSE cf.online_name
			END
		FROM ctrb_fund cf
		WHERE cf.fund_id = @FundID
		)

	-- Return the result of the function
	RETURN (@ReturnVar)

END

GO

