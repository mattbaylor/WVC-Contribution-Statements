USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_fundfromContributionId]    Script Date: 12/30/2012 11:38:18 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/14/2011
-- Description:	Given a contribution id return the fund name
-- =============================================
CREATE FUNCTION [dbo].[wvc_funct_fundfromContributionId] 
(
	-- Add the parameters for the function here
	@ContributionID int
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
		FROM ctrb_contribution_fund ccf
			INNER JOIN ctrb_fund cf ON ccf.fund_id = cf.fund_id
		WHERE ccf.contribution_id = @ContributionID
		)

	-- Return the result of the function
	RETURN (@ReturnVar)

END

GO

