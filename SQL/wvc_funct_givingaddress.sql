USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_givingAddress]    Script Date: 12/30/2012 11:39:25 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/20/2011
-- Description:	Given a giving unit id return the best address
-- =============================================
CREATE FUNCTION [dbo].[wvc_funct_givingAddress] 
(
	-- Add the parameters for the function here
	@GivingUnitID nvarchar(100)
)
RETURNS nvarchar(255)
AS
BEGIN
	-- Declare the return variable here
	DECLARE @Address nvarchar(255)

	-- Add the T-SQL statements to compute the return value here
	SELECT @Address = ca.street_address_1 + CHAR(13) + ca.street_address_2 + CHAR(13) + ca.city + ', ' + ca.state + '   ' + ca.postal_code
		FROM core_person cp
			INNER JOIN core_person_address cpa ON cp.person_id = cpa.person_id
			INNER JOIN core_address ca ON cpa.address_id = ca.address_id
		WHERE cpa.primary_address = 1 AND
			cp.giving_unit_id = @GivingUnitID

	-- Return the result of the function
	RETURN @Address

END

GO

